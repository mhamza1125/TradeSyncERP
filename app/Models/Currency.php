<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Currency extends Model
{
    use LogsActivity;

    protected $fillable = [
        'currency_name',
        'currency_code',
        'symbol',
        'exchange_rate',
        'is_default',
        'status',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:6',
        'is_default'    => 'boolean',
        'status'        => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }
}
