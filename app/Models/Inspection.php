<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inspection extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sample_id',
        'inspection_type_id',
        'report_number',
        'inspection_date',
        'overall_status',
        'remarks',
    ];

    protected $casts = [
        'inspection_date' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }

    public function inspectors()
    {
        return $this->belongsToMany(Employee::class, 'employee_inspection');
    }

    public function results()
    {
        return $this->hasMany(InspectionResult::class);
    }
}
