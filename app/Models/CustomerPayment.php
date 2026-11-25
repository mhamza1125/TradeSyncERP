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
        'customer_invoice_id',
        'transaction_id',
        'payment_date',
        'invoice_reference',
        'foreign_currency',
        'invoiced_amount_fc',
        'deduction_fc',
        'received_fc',
        'wh_tax_percent',
        'wh_tax_amount_fc',
        'exchange_rate',
        'expected_pkr',
        'actual_pkr_received',
        'remittance_charges',
        'pkr_gain_loss',
        'fc_gain_loss',
        'account_id',
        'remarks',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'invoiced_amount_fc' => 'decimal:2',
        'deduction_fc' => 'decimal:2',
        'received_fc' => 'decimal:2',
        'wh_tax_percent' => 'decimal:2',
        'wh_tax_amount_fc' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'expected_pkr' => 'decimal:2',
        'actual_pkr_received' => 'decimal:2',
        'remittance_charges' => 'decimal:2',
        'pkr_gain_loss' => 'decimal:2',
        'fc_gain_loss' => 'decimal:2',
        'voided_at' => 'datetime',
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

    /** Voided records are kept for the audit trail but excluded from balances and totals (docs/FINANCE_RULES.md). */
    public function scopeNotVoided($query)
    {
        return $query->whereNull($this->qualifyColumn('voided_at'));
    }

    public function invoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id');
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }
}
