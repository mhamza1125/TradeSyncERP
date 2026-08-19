<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class InspectionRunDefect extends Model
{
    public const STATUSES = ['open', 'rectified', 'rejected'];

    public const DISPOSITION_CODES = ['MACDF', 'MACSO', 'MACDE'];

    protected $fillable = [
        'inspection_run_section_id',
        'defect_id',
        'severity',
        'size',
        'qty',
        'carton_no',
        'status',
        'disposition_code',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function runSection()
    {
        return $this->belongsTo(InspectionRunSection::class, 'inspection_run_section_id');
    }

    public function defect()
    {
        return $this->belongsTo(Defect::class);
    }

    /**
     * Photos for this defect row are stored as Attachments on the parent
     * InspectionRunSection (same polymorphic target every other section
     * attachment uses), grouped by this per-row task_key — see
     * InspectionRunController::storeSectionAttachment().
     */
    public function taskKey(): string
    {
        return 'defect_row_'.$this->id;
    }

    /**
     * The same Critical/Major => Fail, Minor-only => Conditional, none => Pass
     * rule InspectionRunController::resolveRunVerdict() has always applied —
     * extracted here as a pure function (independent of the Defect catalog
     * lookup the old code did) so it can be unit tested without a database,
     * and reused now that severity is stored as a snapshot on each row.
     *
     * @param  Collection<int, string|null>  $severities
     */
    public static function verdictFromSeverities(Collection $severities): string
    {
        if ($severities->isEmpty()) {
            return 'Pass';
        }

        return $severities->intersect(['critical', 'major'])->isNotEmpty()
            ? 'Fail'
            : 'Conditional';
    }
}
