<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerOrder extends Model
{
    use LogsActivity, SoftDeletes;

    const STATUS_DUE = 'Due';

    const STATUS_DONE = 'Done';

    const STATUSES = [self::STATUS_DUE, self::STATUS_DONE];

    protected $fillable = [
        'order_code',
        'order_number',
        'customer_id',
        'supplier_id',
        'order_date',
        'required_by',
        'status',
        'remarks',
    ];

    protected $casts = [
        'order_date' => 'date',
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

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'inspection_customer_orders');
    }

    public function items()
    {
        return $this->hasMany(CustomerOrderItem::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
