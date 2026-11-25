<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Expense extends Model
{
    use LogsActivity;

    protected $fillable = [
        'expense_head_id',
        'purchase_order_id',
        'account_id',
        'transaction_id',
        'amount',
        'expense_date',
        'description',
        'attachment',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function expenseHead()
    {
        return $this->belongsTo(ExpenseHead::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    /** Voided records are kept for the audit trail but excluded from balances and totals (docs/FINANCE_RULES.md). */
    public function scopeNotVoided($query)
    {
        return $query->whereNull($this->qualifyColumn('voided_at'));
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
