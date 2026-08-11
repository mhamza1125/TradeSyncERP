<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class AqlCalculation extends Model
{
    use LogsActivity;

    protected $fillable = [
        'title',
        'lot_size',
        'inspection_level',
        'aql_critical',
        'aql_major',
        'aql_minor',
        'code_letter',
        'sample_size',
        'ac_critical',
        're_critical',
        'ac_major',
        're_major',
        'ac_minor',
        're_minor',
        'found_critical',
        'found_major',
        'found_minor',
        'verdict',
        'variations',
        'notes',
    ];

    protected $casts = [
        'lot_size' => 'integer',
        'sample_size' => 'integer',
        'ac_critical' => 'integer',
        're_critical' => 'integer',
        'ac_major' => 'integer',
        're_major' => 'integer',
        'ac_minor' => 'integer',
        're_minor' => 'integer',
        'found_critical' => 'integer',
        'found_major' => 'integer',
        'found_minor' => 'integer',
        'variations' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
