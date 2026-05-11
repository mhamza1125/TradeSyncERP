<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionResult extends Model
{
    use LogsActivity;

    protected $fillable = [
        'inspection_id',
        'sample_testing_parameter_id',
        'actual_result',
        'pass_fail',
        'remarks',
        'attachment',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function sampleTestingParameter()
    {
        return $this->belongsTo(SampleTestingParameter::class, 'sample_testing_parameter_id');
    }
}
