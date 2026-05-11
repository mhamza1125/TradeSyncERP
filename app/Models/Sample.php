<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sample extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'sample_code',
        'category_id',
        'customer_id',
        'brand_id',
        'product_name',
        'shipment_reference',
        'receive_date',
        'quantity',
        'priority_level',
        'alert_days',
        'status',
        'remarks',
    ];

    protected $casts = [
        'receive_date' => 'date',
        'status'       => 'string',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    protected static function booted(): void
    {
        static::creating(function (Sample $sample) {
            // sample_code is generated in the controller before save
        });
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function testingParameters()
    {
        return $this->hasMany(SampleTestingParameter::class);
    }

    public function movements()
    {
        return $this->hasMany(SampleMovement::class);
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['Completed', 'Returned'])
            && $this->receive_date->addDays($this->alert_days)->isPast();
    }
}
