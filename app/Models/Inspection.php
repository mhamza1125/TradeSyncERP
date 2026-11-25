<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Inspection extends Model
{
    use LogsActivity;

    protected $fillable = [
        'report_number',
        'inspection_type_id',
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

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }

    public function customerOrders()
    {
        return $this->belongsToMany(CustomerOrder::class, 'inspection_customer_orders');
    }

    public function inspectors()
    {
        return $this->belongsToMany(Employee::class, 'employee_inspection');
    }

    public function runs()
    {
        return $this->hasMany(InspectionRun::class);
    }

    /** Recompute overall_status from the runs (rules: InspectionVerdictService::rollUp, docs/QC_RULES.md). */
    public function syncOverallStatus(): void
    {
        $status = \App\Services\Inspection\InspectionVerdictService::rollUp(
            $this->runs()->get(['id', 'verdict', 'completed_at'])
                ->map(fn ($r) => ['verdict' => $r->verdict, 'finished' => $r->completed_at !== null])
        );

        if ($this->overall_status !== $status) {
            $this->update(['overall_status' => $status]);
        }
    }
}
