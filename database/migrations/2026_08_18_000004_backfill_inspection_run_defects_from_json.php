<?php

use App\Models\Attachment;
use App\Models\InspectionRunSection;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Data migration: copies existing defects_check selections out of
     * InspectionRunSection.data['selections'] JSON into the new
     * inspection_run_defects table, so old and new runs render from the
     * same source. size/carton_no/disposition_code are left null for
     * backfilled rows — that data never existed before this change.
     *
     * Any attachment previously keyed with task_key = "defect_{defect_id}"
     * on the run section is re-keyed to "defect_row_{new_row_id}" so it
     * stays matched to the specific defect entry it belongs to now that a
     * section can hold more than one row for the same catalog defect (e.g.
     * the same defect found in two different sizes). The attachment stays
     * attached to the same InspectionRunSection — only its task_key changes.
     */
    public function up(): void
    {
        $sections = InspectionRunSection::whereHas('section', function ($q) {
            $q->whereIn('slug', ['defect_recording', 'denim_textile_defects']);
        })->get();

        foreach ($sections as $runSection) {
            $selections = collect($runSection->data['selections'] ?? [])
                ->filter(fn ($s) => ! empty($s['selected']) && ! empty($s['defect_id']));

            foreach ($selections as $selection) {
                $defectId = (int) $selection['defect_id'];

                $rowId = DB::table('inspection_run_defects')->insertGetId([
                    'inspection_run_section_id' => $runSection->id,
                    'defect_id' => $defectId,
                    'severity' => in_array($selection['severity'] ?? null, ['critical', 'major', 'minor', 'functional'], true)
                        ? $selection['severity']
                        : null,
                    'qty' => (int) ($selection['quantity'] ?? 1),
                    'status' => 'open',
                    'notes' => $selection['comment'] ?? null,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                Attachment::where('attachable_type', InspectionRunSection::class)
                    ->where('attachable_id', $runSection->id)
                    ->where('task_key', 'defect_'.$defectId)
                    ->update([
                        'task_key' => 'defect_row_'.$rowId,
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Restore attachments to their pre-backfill task_key before dropping
        // the rows, so no attachment is left pointing at a deleted key.
        $rows = DB::table('inspection_run_defects')->get();

        foreach ($rows as $row) {
            Attachment::where('attachable_type', InspectionRunSection::class)
                ->where('attachable_id', $row->inspection_run_section_id)
                ->where('task_key', 'defect_row_'.$row->id)
                ->update(['task_key' => 'defect_'.$row->defect_id]);
        }

        DB::table('inspection_run_defects')->truncate();
    }
};
