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

    public function samples()
    {
        return $this->belongsToMany(Sample::class, 'inspection_samples');
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
}
