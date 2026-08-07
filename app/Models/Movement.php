<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Movement extends Model
{
    use LogsActivity;

    /** Allowed values for recipient_type — who the samples were physically handed to. */
    public const RECIPIENT_TYPES = [
        'Employee' => Employee::class,
        'Supplier' => Supplier::class,
        'Customer' => Customer::class,
    ];

    protected $fillable = [
        'inspection_run_id',
        'recipient_type',
        'recipient_id',
        'order_number',
        'inspection_type_id',
        'issue_date',
        'expected_return_date',
        'actual_return_date',
        'alert_days',
        'remarks',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expected_return_date' => 'date',
        'actual_return_date' => 'date',
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

    /** The inspection type this movement's samples are being issued for (e.g. Inline, Final QC). */
    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /**
     * Resolve the recipient model instance (Employee/Supplier/Customer) that the
     * samples were handed to. recipient_type/recipient_id use simple aliases
     * (see RECIPIENT_TYPES) rather than FQCNs, matching the legacy
     * SampleMovement::moved_by_type/assigned_to_type convention.
     */
    public function getRecipientAttribute(): ?Model
    {
        if (! $this->recipient_type || ! $this->recipient_id) {
            return null;
        }

        $modelClass = self::RECIPIENT_TYPES[$this->recipient_type] ?? null;

        return $modelClass ? $modelClass::find($this->recipient_id) : null;
    }

    public function getRecipientNameAttribute(): ?string
    {
        $recipient = $this->recipient;

        if (! $recipient) {
            return null;
        }

        return match ($this->recipient_type) {
            'Employee' => $recipient->employee_name,
            'Customer' => $recipient->display_name,
            'Supplier' => $recipient->name,
            default => null,
        };
    }

    public function isOverdue(): bool
    {
        return $this->status === 'Issued'
            && $this->expected_return_date
            && $this->expected_return_date->isPast()
            && is_null($this->actual_return_date);
    }
}
