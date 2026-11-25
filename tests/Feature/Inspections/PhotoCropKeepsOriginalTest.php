<?php

namespace Tests\Feature\Inspections;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** QC proposal i2: cropping evidence must never destroy the original photo. */
class PhotoCropKeepsOriginalTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    public function test_crop_keeps_the_original_and_later_crops_only_replace_the_edit(): void
    {
        Storage::fake('public');
        $this->actingAs($this->userWithRole('Admin'));
        $inspection = $this->makeInspection();
        $run = $this->makeRun($inspection, [[$this->makeSection('photos_crop', 'images'), [], 'pending']]);
        $section = $run->runSections()->firstOrFail();

        Storage::disk('public')->put('inspection-sections/1/original.jpg', UploadedFile::fake()->image('o.jpg', 40, 40)->get());
        $photo = $section->attachments()->create([
            'title' => 'p', 'file_name' => 'p.jpg', 'file_path' => 'inspection-sections/1/original.jpg', 'mime_type' => 'image/jpeg',
        ]);

        $crop = fn () => $this->postJson(route('inspections.runs.attachments.edit', [$inspection, $run, $photo]), [
            'image' => UploadedFile::fake()->image('crop.jpg', 20, 20),
        ])->assertOk();

        $crop();
        $photo->refresh();
        $firstEdit = $photo->file_path;
        $this->assertSame('inspection-sections/1/original.jpg', $photo->original_file_path);
        Storage::disk('public')->assertExists('inspection-sections/1/original.jpg');
        Storage::disk('public')->assertExists($firstEdit);

        $crop();
        $photo->refresh();
        $this->assertSame('inspection-sections/1/original.jpg', $photo->original_file_path); // still the real original
        Storage::disk('public')->assertExists('inspection-sections/1/original.jpg');
        Storage::disk('public')->assertMissing($firstEdit); // the intermediate edit is not evidence
        Storage::disk('public')->assertExists($photo->file_path);

        // Deleting the photo removes both files.
        $this->deleteJson(route('inspections.runs.attachments.delete', [$inspection, $run, $photo]))->assertOk();
        Storage::disk('public')->assertMissing('inspection-sections/1/original.jpg');
        Storage::disk('public')->assertMissing($photo->file_path);
    }
}
