<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SampleMovement extends Model
{
    use LogsActivity;

    protected $fillable = [
        'sample_id',
        'moved_by_type',
        'moved_by_id',
        'assigned_to_type',
        'assigned_to_id',
        'issue_date',
        'expected_return_date',
        'actual_return_date',
        'alert_days',
        'status',
        'remarks',
    ];

    protected $casts = [
        'issue_date'           => 'date',
        'expected_return_date' => 'date',
        'actual_return_date'   => 'date',
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

    public function movedByEmployee()
    {
        return $this->belongsTo(Employee::class, 'moved_by_id');
    }

    public function movedByUser()
    {
        return $this->belongsTo(User::class, 'moved_by_id');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'Issued'
            && $this->expected_return_date
            && $this->expected_return_date->isPast()
            && is_null($this->actual_return_date);
    }
}
