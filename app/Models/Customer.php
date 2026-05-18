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
        'brand',
        'currency_id',
        'opening_balance',
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

    public function samples()
    {
        return $this->hasMany(Sample::class);
    }

    public function payments()
    {
        return $this->hasMany(CustomerPayment::class);
    }

    public function invoices()
    {
        return $this->hasMany(CustomerInvoice::class);
    }

    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'customer_supplier');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }
}
