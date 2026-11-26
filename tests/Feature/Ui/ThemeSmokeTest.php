<?php

namespace Tests\Feature\Ui;

use App\Models\Attachment;
use App\Models\CompanySetting;
use App\Models\Defect;
use App\Models\InspectionRunDefect;
use App\Models\InspectionSection;
use App\Services\Inspection\InspectionRunLockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\Support\BuildsInspections;
use Tests\TestCase;

/**
 * Stage 1 theme (docs/DESIGN.md): the app shell renders, the sidebar shows only links the user
 * may open (and hiding never replaces the server checks), the restyled run PDF renders, and
 * user data stays escaped.
 */
class ThemeSmokeTest extends TestCase
{
    use BuildsInspections, RefreshDatabase;

    private const PHOTO_DIR = 'theme-smoke-test';

    protected function tearDown(): void
    {
        File::deleteDirectory(storage_path('app/public/'.self::PHOTO_DIR));
        parent::tearDown();
    }

    private function sidebarHtml(string $html): string
    {
        $start = strpos($html, '<nav class="nxl-navigation"');
        $end = strpos($html, '</nav>', $start);

        return substr($html, $start, $end - $start);
    }

    // ── Pages render for the admin ──────────────────────────────────────────

    public function test_key_pages_render_for_the_admin(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $inspection = $this->makeInspection();
        $run = $this->makeRun($inspection, [[$this->makeSection('packing_check', 'checklist'), [], 'pending']]);

        foreach ([
            route('dashboard'),
            route('inspections.index'),
            route('inspections.show', $inspection),
            route('inspections.edit', $inspection),
            route('inspections.runs.edit', [$inspection, $run]),
            route('samples.index'),
            route('customer-invoices.index'),
            route('masters.colors.index'),
            route('activities.index'),
            route('profile.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_the_layout_loads_the_brand_layer_and_no_remote_fonts(): void
    {
        $this->actingAs($this->userWithRole('Admin'));

        $html = $this->get(route('dashboard'))->assertOk()->getContent();

        $this->assertStringContainsString('assets/css/brand.css', $html);
        $this->assertStringContainsString('assets/js/sidebar-nav.js', $html);
        $this->assertStringContainsString('class="ts-skip-link"', $html);
        $this->assertDoesNotMatchRegularExpression('#<(link|script)[^>]+(https?:)?//(?!127\.0\.0\.1|localhost)[^"\']*(fonts|cdn|maxcdn)#i', $html);
        $this->assertStringNotContainsString('fonts.googleapis.com', file_get_contents(public_path('assets/css/theme.min.css')));
    }

    // ── Sidebar shows only what the user may open; the server still refuses ──

    public function test_an_employee_sees_only_permitted_links_and_hidden_routes_still_return_403(): void
    {
        $this->actingAs($this->userWithRole('Employee'));

        $nav = $this->sidebarHtml($this->get(route('dashboard'))->assertOk()->getContent());

        $this->assertStringContainsString('href="'.route('inspections.index').'"', $nav);
        $this->assertStringContainsString('href="'.route('samples.index').'"', $nav);

        foreach (['customer-invoices.index', 'masters.colors.index', 'admin.users.index', 'salary.index', 'activities.index'] as $name) {
            $this->assertStringNotContainsString('href="'.route($name).'"', $nav, "{$name} link shown to an Employee");
            $this->get(route($name))->assertForbidden();
        }
        $this->assertStringNotContainsString('Finance', $nav);
        $this->assertStringNotContainsString('Administration', $nav);
    }

    public function test_a_user_without_a_role_sees_only_the_dashboard(): void
    {
        $this->actingAs($this->userWithoutRole());

        $html = $this->get(route('dashboard'))->assertOk()->getContent();
        $nav = $this->sidebarHtml($html);

        $this->assertStringContainsString('href="'.route('dashboard').'"', $nav);
        $this->assertSame(1, substr_count($nav, 'data-nav-group-header='), 'only the Overview group');
        foreach (['inspections.index', 'samples.index', 'masters.customers.index'] as $name) {
            $this->assertStringNotContainsString('href="'.route($name).'"', $nav);
            $this->get(route($name))->assertForbidden();
        }
        // The activity bell is hidden too (its feed was already empty for this user).
        $this->assertStringNotContainsString('aria-label="Recent activity"', $html);
    }

    public function test_colors_and_sizes_links_need_their_own_permission(): void
    {
        // Old sidebar: shown to anyone with e.g. accounts.index, although the routes need colors.index / sizes.index.
        $this->actingAs($this->userWithPermissions('accounts.index'));

        $nav = $this->sidebarHtml($this->get(route('dashboard'))->getContent());

        $this->assertStringContainsString('href="'.route('masters.accounts.index').'"', $nav);
        $this->assertStringNotContainsString('href="'.route('masters.colors.index').'"', $nav);
        $this->assertStringNotContainsString('href="'.route('masters.sizes.index').'"', $nav);
        $this->get(route('masters.colors.index'))->assertForbidden();
        $this->get(route('masters.sizes.index'))->assertForbidden();
    }

    public function test_allowance_types_alone_is_enough_to_show_its_link(): void
    {
        // Old sidebar: its group was gated by other finance permissions only, so the link was hidden.
        $this->actingAs($this->userWithPermissions('allowance-types.index'));

        $nav = $this->sidebarHtml($this->get(route('dashboard'))->getContent());

        $this->assertStringContainsString('href="'.route('allowance-types.index').'"', $nav);
        $this->get(route('allowance-types.index'))->assertOk();
    }

    public function test_the_admin_sees_every_group_and_the_activity_bell(): void
    {
        $this->actingAs($this->userWithRole('Admin'));

        $html = $this->get(route('dashboard'))->getContent();
        $nav = $this->sidebarHtml($html);

        foreach (['Overview', 'Operations', 'Quality control', 'Finance', 'HR &amp; payroll', 'Master data', 'Administration'] as $group) {
            $this->assertStringContainsString($group, $nav);
        }
        $this->assertStringContainsString('aria-label="Recent activity"', $html);
        $this->assertStringContainsString('aria-current="page"', $nav);
    }

    // ── Run PDF ──────────────────────────────────────────────────────────────

    private function pdfFor($inspection, $run): string
    {
        $response = $this->get(route('inspections.runs.export-pdf', [$inspection, $run]))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $body = $response->getContent();
        $this->assertStringStartsWith('%PDF', $body);
        $this->assertGreaterThan(5000, strlen($body));

        return $body;
    }

    private function photo(int $i): string
    {
        $path = self::PHOTO_DIR."/p{$i}.png";
        File::ensureDirectoryExists(storage_path('app/public/'.self::PHOTO_DIR));
        $img = imagecreatetruecolor(240, 180);
        imagefill($img, 0, 0, imagecolorallocate($img, 40 + $i, 90, 140));
        imagepng($img, storage_path('app/public/'.$path));
        imagedestroy($img);

        return $path;
    }

    public function test_the_run_pdf_renders_for_pass_fail_photo_and_empty_fixtures(): void
    {
        $this->actingAs($this->userWithRole('Admin'));
        $inspection = $this->makeInspection();
        $checklist = $this->makeSection('packing_check', 'checklist');
        $defectsLib = $this->makeSection('defect_recording', 'defects');
        $images = $this->makeSection('product_screening', 'images');

        // Pass run (finished, nothing found)
        $pass = $this->makeRun($inspection, [[$checklist, ['items' => [['label' => 'Cartons sealed', 'result' => 'pass']]], 'complete']],
            ['verdict' => 'Pass', 'completed_at' => now()]);
        // Fail run (a major defect row)
        $fail = $this->makeRun($inspection, [[$defectsLib, [], 'complete']], ['verdict' => 'Fail', 'completed_at' => now()]);
        $major = Defect::create(['defect_name' => 'Open seam', 'severity' => 'major', 'status' => true]);
        InspectionRunLockService::bypass(fn () => InspectionRunDefect::create([
            'inspection_run_section_id' => $fail->runSections()->firstOrFail()->id, 'defect_id' => $major->id,
            'severity' => 'major', 'qty' => 2, 'status' => 'open', 'sort_order' => 0,
        ]));
        // Many photos
        $photos = $this->makeRun($inspection, [[$images, [], 'complete']]);
        $section = $photos->runSections()->firstOrFail();
        foreach (range(1, 12) as $i) {
            $section->attachments()->create(['title' => "p{$i}", 'file_name' => "p{$i}.png", 'file_path' => $this->photo($i),
                'mime_type' => 'image/png', 'file_size' => 1000, 'attachment_type' => 'gallery', 'sort_order' => $i]);
        }
        // Empty sections
        $empty = $this->makeRun($inspection, [[$checklist, [], 'pending'], [$images, [], 'pending']]);

        foreach ([$pass, $fail, $photos, $empty] as $run) {
            $this->pdfFor($inspection, $run);
        }
        $this->assertSame(12, Attachment::count());
    }

    public function test_the_restyled_pdf_keeps_user_data_escaped_and_php_disabled(): void
    {
        $this->assertFalse(config('dompdf.options.isPhpEnabled'));

        $this->actingAs($this->userWithRole('Admin'));
        // Row 1 explicitly: CompanySetting::current() can't create id 1 itself ('id' is not fillable;
        // see docs/AUDIT_QUESTIONS.md Q44), so after earlier tests it would read another row.
        CompanySetting::unguarded(fn () => CompanySetting::query()->updateOrCreate(['id' => 1], ['company_name' => 'Acme <script>alert(1)</script>', 'address' => '<b>Street</b>']));
        $inspection = $this->makeInspection(['remarks' => '<img src=x onerror=alert(2)>']);
        $section = InspectionSection::firstOrCreate(['slug' => 'packing_check'], [
            'name' => 'Packing <i>Check</i>', 'section_type' => 'checklist', 'default_data' => [], 'is_active' => true, 'skip_policy' => 'mandatory',
        ]);
        $run = $this->makeRun($inspection, [[$section, ['items' => [['label' => 'Seal', 'result' => 'pass']]], 'complete']]);
        InspectionRunLockService::bypass(fn () => $run->runSections()->update(['notes' => '<script>alert(3)</script>']));

        $html = view('exports.inspection-run-pdf', [
            'inspection' => $inspection->fresh()->load(['inspectionType', 'inspectors', 'customerOrders']),
            'runs' => collect([$run->fresh()->load(['sample.customer', 'sample.category', 'sampleColor', 'runSections.section', 'runSections.attachments', 'runSections.defects.defect', 'aql.sizeBreakdowns'])]),
            'imgBase64' => fn () => null,
            'defects' => collect(),
            'logoBase64' => null,
        ])->render();

        foreach (['<script>alert(1)</script>', '<b>Street</b>', '<img src=x onerror=alert(2)>', '<script>alert(3)</script>', 'Packing <i>Check</i>'] as $raw) {
            $this->assertStringNotContainsString($raw, $html);
        }
        $this->assertStringContainsString('Acme &lt;script&gt;alert(1)&lt;/script&gt;', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(3)&lt;/script&gt;', $html);
        $this->assertStringContainsString('<meta name="pdf-page-numbers" content="1">', $html);
    }
}
