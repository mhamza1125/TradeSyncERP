<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustomerInvoice extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'bank_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'amount_paid',
        'amount_due',
        'status',
        'remarks',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'amount_due' => 'decimal:2',
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

    public function bank()
    {
        return $this->belongsTo(Bank::class);
    }

    public function items()
    {
        return $this->hasMany(CustomerInvoiceItem::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    /** Overdue is derived, never typed: past due, money still owed, not Paid/Cancelled (audit F-16; docs/FINANCE_RULES.md). */
    public function scopeOverdue($query)
    {
        return $query->whereNotIn($this->qualifyColumn('status'), ['Paid', 'Cancelled'])
            ->where($this->qualifyColumn('amount_due'), '>', 0)
            ->whereDate($this->qualifyColumn('due_date'), '<', now()->toDateString());
    }

    public function isOverdue(): bool
    {
        return ! in_array($this->status, ['Paid', 'Cancelled'], true)
            && bccomp((string) $this->amount_due, '0', 2) > 0
            && $this->due_date !== null && $this->due_date->lt(now()->startOfDay());
    }

    /**
     * Sum of a money column per currency (the customer's currency; the invoice stores none,
     * see docs/FINANCE_REVIEW.md FN-4). Amounts in different currencies are never added together.
     *
     * @return array<string, string> currency code => decimal string, sorted by code
     */
    public static function totalsByCurrency(string $column, array $excludeStatuses): array
    {
        $default = Currency::where('is_default', true)->value('currency_code') ?? 'PKR';

        return static::query()
            ->leftJoin('customers', 'customers.id', '=', 'customer_invoices.customer_id')
            ->leftJoin('currencies', 'currencies.id', '=', 'customers.currency_id')
            ->whereNotIn('customer_invoices.status', $excludeStatuses)
            ->groupBy('code')
            ->orderBy('code')
            ->selectRaw('COALESCE(currencies.currency_code, ?) AS code, SUM(customer_invoices.'.$column.') AS total', [$default])
            ->toBase()
            ->pluck('total', 'code')
            ->map(fn ($t) => bcadd((string) $t, '0', 2))
            ->all();
    }

    /** Status to show: the stored status, or "Overdue" when that is derived. */
    public function getDisplayStatusAttribute(): string
    {
        return $this->isOverdue() ? 'Overdue' : (string) $this->status;
    }
}
