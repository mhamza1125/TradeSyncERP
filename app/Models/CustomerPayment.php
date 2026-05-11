<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerPayment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'customer_id',
        'transaction_id',
        'payment_date',
        'invoice_reference',
        'foreign_currency',
        'invoiced_amount_fc',
        'deduction_fc',
        'received_fc',
        'exchange_rate',
        'expected_pkr',
        'actual_pkr_received',
        'pkr_gain_loss',
        'fc_gain_loss',
        'account_id',
        'remarks',
    ];

    protected $casts = [
        'payment_date'        => 'date',
        'invoiced_amount_fc'  => 'decimal:2',
        'deduction_fc'        => 'decimal:2',
        'received_fc'         => 'decimal:2',
        'exchange_rate'       => 'decimal:6',
        'expected_pkr'        => 'decimal:2',
        'actual_pkr_received' => 'decimal:2',
        'pkr_gain_loss'       => 'decimal:2',
        'fc_gain_loss'        => 'decimal:2',
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

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
