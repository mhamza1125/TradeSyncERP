<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tools\ProcessQcTestRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Imagick;
use ImagickException;
use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;
use Throwable;

/**
 * QC image-inspection TEST tool.
 *
 * Validates the blur-score + OCR-confidence approach against real product
 * photos. Deliberately NOT wired into the QC/Inspection workflow: no
 * pass/fail thresholds, no database table, no queue job — just raw numbers
 * on screen so real thresholds can be picked later. Uploaded images live in
 * storage/app/qc-test-temp/ (gitignored) only long enough to preview/re-run.
 */
class QcTestController extends Controller
{
    /**
     * Word-level row in Tesseract TSV output (see TSV column "level").
     */
    private const TSV_WORD_LEVEL = 5;

    /**
     * Longest-side cap (px) images are downscaled to before the plain-PHP
     * blur convolution — keeps memory bounded on shared-hosting PHP-FPM
     * pools regardless of source photo resolution. See calculateBlurScore().
     *
     * Measured (not guessed): decode + grayscale + Lanczos resize + PHP
     * float-array export peaks at ~56-74MB for real photos from 6MP to a
     * simulated 48MP (8000x6000), independent of source size, at this cap —
     * comfortably under even a tight 128M PHP memory_limit. 1800px measured
     * ~144-148MB instead, which needs 256M+ — too easy to exceed on a
     * shared-hosting plan without checking first, so this stays at 1200.
     */
    private const MAX_BLUR_DIMENSION = 1200;

    /** Defensive backstop only, in case the resize above doesn't apply — see calculateBlurScore(). */
    private const MAX_BLUR_PIXELS = 2_000_000;

    public function __construct()
    {
        $this->middleware('permission:qc-test.index');
    }

    public function index(): View
    {
        $this->pruneOldTempFiles();

        return view('tools.qc-test.index', [
            'results' => null,
            'preview' => null,
            'existingPath' => null,
            'originalName' => null,
            'psm' => 6,
            'containsText' => true,
        ]);
    }

    public function process(ProcessQcTestRequest $request): View|RedirectResponse
    {
        $tempDir = $this->tempDir();

        $storedPath = null;
        $relativePath = null;
        $originalName = null;

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension() ?: 'dat';
            $relativePath = Str::uuid()->toString().'.'.$extension;

            try {
                $file->move($tempDir, $relativePath);
            } catch (Throwable $e) {
                return redirect()->route('tools.qc-test.index')
                    ->with('error', 'Could not save the uploaded file to '.$tempDir.': '.$e->getMessage());
            }

            $storedPath = $tempDir.DIRECTORY_SEPARATOR.$relativePath;
        } elseif ($request->filled('existing_path')) {
            // basename() strips any directory traversal attempt from the hidden field.
            $relativePath = basename((string) $request->input('existing_path'));
            $candidate = $tempDir.DIRECTORY_SEPARATOR.$relativePath;

            if (! is_file($candidate)) {
                return redirect()->route('tools.qc-test.index')
                    ->with('error', 'That previously uploaded image is no longer available on this server. Please upload it again.');
            }

            $storedPath = $candidate;
        }

        $psm = (int) $request->input('psm');
        $containsText = $request->boolean('contains_text');

        $results = [
            'blur_score' => null,
            'blur_error' => null,
            'blur_ms' => null,
            'ocr' => null,
            'ocr_error' => null,
            'ocr_ms' => null,
        ];

        $start = microtime(true);
        try {
            $results['blur_score'] = $this->calculateBlurScore($storedPath);
        } catch (ImagickException $e) {
            $results['blur_error'] = 'Imagick could not process this image (corrupt file or unsupported format): '.$e->getMessage();
        } catch (Throwable $e) {
            $results['blur_error'] = 'Unexpected error computing blur score: '.$e->getMessage();
        }
        $results['blur_ms'] = round((microtime(true) - $start) * 1000, 1);

        if ($containsText) {
            $start = microtime(true);
            try {
                $results['ocr'] = $this->runOcr($storedPath, $psm);
            } catch (TesseractOcrException $e) {
                $results['ocr_error'] = 'Tesseract OCR failed: '.$e->getMessage();
            } catch (Throwable $e) {
                $results['ocr_error'] = 'Unexpected error running OCR: '.$e->getMessage();
            }
            $results['ocr_ms'] = round((microtime(true) - $start) * 1000, 1);
        }

        return view('tools.qc-test.index', [
            'results' => $results,
            'preview' => $this->buildPreviewDataUri($storedPath),
            'existingPath' => $relativePath,
            'originalName' => $originalName,
            'psm' => $psm,
            'containsText' => $containsText,
        ]);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $relativePath = basename((string) $request->input('existing_path'));

        if ($relativePath !== '' && $relativePath !== '.' && $relativePath !== '..') {
            $candidate = $this->tempDir().DIRECTORY_SEPARATOR.$relativePath;
            if (is_file($candidate)) {
                @unlink($candidate);
            }
        }

        return redirect()->route('tools.qc-test.index');
    }

    /**
     * Blur/sharpness score via Laplacian-variance: grayscale the image,
     * convolve with the edge kernel [0,1,0,1,-4,1,0,1,0], then take the
     * standard deviation squared (i.e. variance) of the result. Lower
     * scores mean flatter/less-detailed edges → more likely blurry.
     * No pass/fail threshold is applied here on purpose.
     *
     * NOTE: Imagick::convolveImage()/morphology() are deliberately NOT used
     * here. On this build (ImageMagick 7.1.1 Q16-HDRI, PECL imagick on
     * Windows) convolving with this zero-sum kernel produces garbage pixel
     * values (~1e12 magnitude instead of small deltas) — reproduced with a
     * synthetic test image, independent of source format. Root cause looks
     * like an HDRI-depth quantum-scaling bug in that ImageMagick build, not
     * a coding error (kernel scale/normalize flags didn't fix it either).
     * Instead we read raw grayscale pixel intensities via Imagick
     * (exportImagePixels(), confirmed to return correct normalised 0-1
     * values) and apply the 3x3 kernel by hand in PHP, which is both
     * correct and portable across Imagick/ImageMagick builds. Pixel
     * intensities are scaled to the conventional 0-255 range first so
     * scores land in the same ballpark as common OpenCV blur-score writeups
     * (typically tens to low thousands for real photos).
     */
    private function calculateBlurScore(string $path): float
    {
        $imagick = new Imagick($path);

        // Multi-frame formats (e.g. animated GIF, multi-page TIFF/PDF): just
        // score the first frame — this tool is for single product photos.
        if ($imagick->getNumberImages() > 1) {
            $imagick->setIteratorIndex(0);
        }

        $imagick->setImageColorspace(Imagick::COLORSPACE_GRAY);

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        if ($width < 3 || $height < 3) {
            throw new \RuntimeException('Image is too small to compute a blur score (minimum 3x3 pixels).');
        }

        // The convolution below runs in plain PHP (see note above) and pulls
        // every pixel into memory as a float — measured ~37MB per megapixel
        // for that array (plus Imagick's own decoded copy held briefly
        // alongside it). A phone photo today is routinely 12–48MP, which
        // would exceed typical shared-hosting memory_limit (128–256M is
        // common) and trigger an uncatchable "memory exhausted" fatal. So:
        // downscale first via Imagick's own (much more memory-efficient)
        // resizer — bounds every image to the same footprint regardless of
        // source resolution, which also makes scores comparable across
        // photos taken on different phones/cameras. Only ever shrinks,
        // never enlarges a smaller image.
        if ($width > self::MAX_BLUR_DIMENSION || $height > self::MAX_BLUR_DIMENSION) {
            $imagick->resizeImage(self::MAX_BLUR_DIMENSION, self::MAX_BLUR_DIMENSION, Imagick::FILTER_LANCZOS, 1, true);
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
        }

        // Defensive backstop only — the resize above should always bring us
        // under this, but guard anyway against a pathological aspect ratio
        // or a resize that silently no-ops on some ImageMagick build.
        if ($width * $height > self::MAX_BLUR_PIXELS) {
            throw new \RuntimeException(sprintf(
                'Image is %dx%d (%.1fMP) even after downscaling — larger than this test tool\'s %dMP safety cap.',
                $width,
                $height,
                ($width * $height) / 1_000_000,
                self::MAX_BLUR_PIXELS / 1_000_000
            ));
        }

        /** @var array<int, float> $pixels row-major, one float per pixel, range 0.0–1.0 */
        $pixels = $imagick->exportImagePixels(0, 0, $width, $height, 'I', Imagick::PIXEL_FLOAT);

        $imagick->clear();
        $imagick->destroy();

        foreach ($pixels as &$value) {
            $value *= 255.0;
        }
        unset($value);

        // Kernel [0,1,0,1,-4,1,0,1,0] applied as a "valid" convolution
        // (border pixels skipped — no padding assumptions to argue about).
        $sum = 0.0;
        $sumSquares = 0.0;
        $count = 0;

        for ($y = 1; $y < $height - 1; $y++) {
            $rowOffset = $y * $width;
            $rowAbove = $rowOffset - $width;
            $rowBelow = $rowOffset + $width;

            for ($x = 1; $x < $width - 1; $x++) {
                $laplacian = $pixels[$rowAbove + $x]
                    + $pixels[$rowOffset + $x - 1]
                    - 4 * $pixels[$rowOffset + $x]
                    + $pixels[$rowOffset + $x + 1]
                    + $pixels[$rowBelow + $x];

                $sum += $laplacian;
                $sumSquares += $laplacian * $laplacian;
                $count++;
            }
        }

        $mean = $sum / $count;
        $variance = ($sumSquares / $count) - ($mean * $mean);

        return round($variance, 4);
    }

    /**
     * OCR via thiagoalessio/tesseract_ocr, using Tesseract's built-in "tsv"
     * config file so we get per-word bounding boxes + confidence, not just
     * raw text. TSV columns: level page_num block_num par_num line_num
     * word_num left top width height conf text. Word-level rows are level 5.
     */
    private function runOcr(string $path, int $psm): array
    {
        $ocr = new TesseractOCR($path);
        $ocr->lang('eng'); // only language pack currently installed
        $ocr->psm($psm);

        if ($executable = env('TESSERACT_PATH')) {
            $ocr->executable($executable);
        }

        $tsv = $ocr->tsv()->run();

        return $this->parseTsv($tsv);
    }

    private function parseTsv(string $tsv): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($tsv));
        $header = str_getcsv((string) array_shift($lines), "\t");

        $words = [];
        $confidences = [];
        $textParts = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            // Cap the split to the header's column count: the "text" column is
            // last and, in rare cases, can itself contain a literal tab.
            $cols = explode("\t", $line, count($header));
            if (count($cols) !== count($header)) {
                continue;
            }
            $row = array_combine($header, $cols);

            if ((int) ($row['level'] ?? 0) !== self::TSV_WORD_LEVEL) {
                continue;
            }

            $text = trim($row['text'] ?? '');
            if ($text === '') {
                continue;
            }

            $confidence = (float) ($row['conf'] ?? -1);

            $words[] = ['text' => $text, 'confidence' => $confidence];
            $textParts[] = $text;
            if ($confidence >= 0) {
                $confidences[] = $confidence;
            }
        }

        return [
            'text' => implode(' ', $textParts),
            'word_count' => count($words),
            'average_confidence' => count($confidences) > 0
                ? round(array_sum($confidences) / count($confidences), 2)
                : null,
            'words' => $words,
        ];
    }

    private function buildPreviewDataUri(?string $path): ?string
    {
        if (! $path || ! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'application/octet-stream';
        $data = file_get_contents($path);

        if ($data === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($data);
    }

    private function tempDir(): string
    {
        $dir = storage_path('app/qc-test-temp');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /**
     * There's no DB row and no queue job tracking these uploads — just files
     * on disk — so nothing else ever removes them. On a shared-hosting box
     * where several people actually use this over days/weeks (as opposed to
     * one person testing locally for an afternoon), that accumulates
     * unbounded. Opportunistically sweep anything older than a day on each
     * page load rather than adding a scheduled command for a test tool.
     */
    private function pruneOldTempFiles(): void
    {
        $cutoff = time() - 86400;

        foreach (glob($this->tempDir().DIRECTORY_SEPARATOR.'*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
