<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Movement extends Model
{
    use LogsActivity;

    protected $fillable = [
        'inspection_run_id',
        'issue_date',
        'expected_return_date',
        'actual_return_date',
        'alert_days',
        'remarks',
        'status',
    ];

    protected $casts = [
        'issue_date'           => 'date',
        'expected_return_date' => 'date',
        'actual_return_date'   => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    public function items()
    {
        return $this->hasMany(MovementItem::class);
    }

    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'movement_employees');
    }

    public function inspectionRun()
    {
        return $this->belongsTo(InspectionRun::class);
    }

    public function isOverdue(): bool
    {
        return $this->status === 'Issued'
            && $this->expected_return_date
            && $this->expected_return_date->isPast()
            && is_null($this->actual_return_date);
    }
}
