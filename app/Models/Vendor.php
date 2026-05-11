<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Vendor extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'vendor_name',
        'company_name',
        'phone',
        'email',
        'address',
        'payment_terms',
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

    public function bills()
    {
        return $this->hasMany(VendorBill::class);
    }
}
