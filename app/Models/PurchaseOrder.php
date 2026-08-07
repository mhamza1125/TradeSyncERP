<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseOrder extends Model
{
    use LogsActivity, SoftDeletes;

    const STATUS_UNPAID = 'Unpaid';

    const STATUS_PARTIALLY_PAID = 'Partially Paid';

    const STATUS_PAID = 'Paid';

    const STATUSES = [self::STATUS_UNPAID, self::STATUS_PARTIALLY_PAID, self::STATUS_PAID];

    /** Default expense head new payments are booked against. */
    const EXPENSE_HEAD_NAME = 'Purchase Orders';

    protected $fillable = [
        'po_number',
        'po_date',
        'total_amount',
        'amount_paid',
        'amount_due',
        'status',
        'remarks',
    ];

    protected $casts = [
        'po_date' => 'date',
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

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    /** Payments recorded against this PO — each one is an Expense row. */
    public function expenses()
    {
        return $this->hasMany(Expense::class)->latest('expense_date');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Recompute amount_paid / amount_due / status from the sum of linked
     * Expense payments. The only place that should be called after a
     * payment is recorded, updated, or removed — mirrors
     * CustomerInvoiceController::syncInvoiceStatus().
     */
    public function recalculatePaymentStatus(): void
    {
        $paid = $this->expenses()->sum('amount');
        $due = max(0, round($this->total_amount - $paid, 2));

        $status = self::STATUS_UNPAID;
        if ($paid > 0 && $due > 0) {
            $status = self::STATUS_PARTIALLY_PAID;
        } elseif ($paid > 0 && $due <= 0) {
            $status = self::STATUS_PAID;
        }

        $this->update([
            'amount_paid' => $paid,
            'amount_due' => $due,
            'status' => $status,
        ]);
    }
}
