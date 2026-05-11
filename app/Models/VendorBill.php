<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VendorBill extends Model
{
    use LogsActivity;

    protected $fillable = [
        'vendor_id',
        'bill_number',
        'bill_date',
        'due_date',
        'total_amount',
        'status',
        'remarks',
        'transaction_id',
    ];

    protected $casts = [
        'bill_date'    => 'date',
        'due_date'     => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items()
    {
        return $this->hasMany(VendorBillItem::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function inspections()
    {
        return $this->belongsToMany(Inspection::class, 'vendor_bill_inspections');
    }

    public function recalculateTotal(): void
    {
        $this->total_amount = $this->items()->sum('line_total');
        $this->save();
    }
}
