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
        'report_number',
        'inspection_date',
        'inspector_type',
        'inspector_id',
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

    public function results()
    {
        return $this->hasMany(InspectionResult::class);
    }

    public function vendorBills()
    {
        return $this->belongsToMany(VendorBill::class, 'vendor_bill_inspections');
    }

    public function inspector()
    {
        if ($this->inspector_type === 'Employee') {
            return $this->belongsTo(Employee::class, 'inspector_id');
        }
        return $this->belongsTo(Vendor::class, 'inspector_id');
    }
}
