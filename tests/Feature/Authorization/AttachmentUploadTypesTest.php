<?php

namespace Tests\Feature\Authorization;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/** Audit F-06: generic attachments get the same type allowlist as inspection run uploads. */
class AttachmentUploadTypesTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->actingAs($this->userWithPermissions('customers.edit'));
        $this->customer = Customer::create(['customer_name' => 'C', 'contact_person' => 'X', 'status' => true]);
    }

    private function upload(UploadedFile $file)
    {
        return $this->from('/back')->post(route('attachments.store', ['customers', $this->customer->id]), [
            'attachments' => [$file],
            'attachment_titles' => ['T'],
        ]);
    }

    /**
     * A real temp file, so the type is sniffed from the content with finfo exactly as for a
     * browser upload (UploadedFile::fake() derives the type from the file name instead).
     */
    private function realFile(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    public static function rejected(): array
    {
        return [
            'html' => ['evil.html', '<html><script>alert(1)</script></html>'],
            'svg' => ['evil.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
            'php' => ['evil.php', '<?php echo 1;'],
            'html renamed to pdf' => ['evil.pdf', '<html><script>alert(1)</script></html>'],
        ];
    }

    #[DataProvider('rejected')]
    public function test_dangerous_types_are_rejected(string $name, string $content): void
    {
        $this->upload($this->realFile($name, $content))
            ->assertRedirect('/back')
            ->assertSessionHasErrors('attachments.0');

        $this->assertSame(0, $this->customer->attachments()->count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_images_pdf_and_office_files_are_accepted(): void
    {
        $files = [
            UploadedFile::fake()->image('photo.jpg'),
            UploadedFile::fake()->image('photo.png'),
            UploadedFile::fake()->createWithContent('doc.pdf', "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"),
        ];

        foreach ($files as $file) {
            $this->upload($file)->assertSessionHasNoErrors();
        }

        $this->assertSame(3, $this->customer->attachments()->count());
    }
}
