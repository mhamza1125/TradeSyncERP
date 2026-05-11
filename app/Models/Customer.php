<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'customer_name',
        'contact_person',
        'phone',
        'email',
        'address',
        'currency_id',
        'default_currency',
        'opening_balance',
        'opening_balance_currency',
        'status',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'status'          => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function brands()
    {
        return $this->hasMany(Brand::class);
    }

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }
}
