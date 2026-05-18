<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionResult extends Model
{
    use LogsActivity;

    protected $fillable = [
        'inspection_run_id',
        'sample_id',
        'testing_parameter_id',
        'status',
        'defect_id',
        'defect_severity',
        'remarks',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function inspectionRun()
    {
        return $this->belongsTo(InspectionRun::class);
    }

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }

    public function testingParameter()
    {
        return $this->belongsTo(TestingParameter::class, 'testing_parameter_id');
    }

    public function defect()
    {
        return $this->belongsTo(Defect::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
