<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerOrder extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'order_code',
        'customer_id',
        'order_date',
        'required_by',
        'status',
        'remarks',
    ];

    protected $casts = [
        'order_date'  => 'date',
        'required_by' => 'date',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_customer_orders');
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItem::class);
    }
}
