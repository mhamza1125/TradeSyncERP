<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\GuardsFinishedRun;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionRunAql extends Model
{
    use GuardsFinishedRun, LogsActivity;

    protected $table = 'inspection_run_aql';

    protected $fillable = [
        'inspection_run_id',
        'lot_size', 'inspection_level', 'sample_size', 'code_letter',
        'aql_critical', 'aql_major', 'aql_minor',
        'ac_critical', 're_critical',
        'ac_major',    're_major',
        'ac_minor',    're_minor',
        'found_critical', 'found_major', 'found_minor',
        'verdict', 'variations',
        'sample_size_critical', 'code_letter_critical',
        'sample_size_major', 'code_letter_major',
        'sample_size_minor', 'code_letter_minor',
        'standard_ref', 'counts_source',
    ];

    protected $casts = [
        'lot_size' => 'integer',
        'sample_size' => 'integer',
        'sample_size_critical' => 'integer',
        'sample_size_major' => 'integer',
        'sample_size_minor' => 'integer',
        'aql_critical' => 'float',
        'aql_major' => 'float',
        'aql_minor' => 'float',
        'found_critical' => 'integer',
        'found_major' => 'integer',
        'found_minor' => 'integer',
        'variations' => 'array',
    ];

    public function run()
    {
        return $this->belongsTo(InspectionRun::class, 'inspection_run_id');
    }

    public function sizeBreakdowns()
    {
        return $this->hasMany(InspectionRunAqlSizeBreakdown::class, 'run_aql_id')
            ->orderBy('sort_order');
    }

    // Verdict rules live only in AqlCalculationService::verdict() (docs/QC_RULES.md).

    public function owningRun(): ?InspectionRun
    {
        return InspectionRun::find($this->inspection_run_id);
    }

    /** Plan, counts and verdict; not the variations JSON. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'inspection_run_id', 'lot_size', 'inspection_level', 'code_letter', 'sample_size',
                'aql_critical', 'aql_major', 'aql_minor', 'ac_critical', 're_critical', 'ac_major', 're_major',
                'ac_minor', 're_minor', 'found_critical', 'found_major', 'found_minor', 'verdict',
                'sample_size_critical', 'sample_size_major', 'sample_size_minor', 'standard_ref', 'counts_source',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
