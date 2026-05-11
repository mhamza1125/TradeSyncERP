<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendorBillItem extends Model
{
    protected $fillable = [
        'vendor_bill_id',
        'description',
        'quantity',
        'unit',
        'unit_price',
        'line_total',
    ];

    protected $casts = [
        'quantity'   => 'decimal:3',
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        $recalc = fn (VendorBillItem $item) => $item->bill->recalculateTotal();

        static::saved($recalc);
        static::deleted($recalc);
    }

    public function bill()
    {
        return $this->belongsTo(VendorBill::class, 'vendor_bill_id');
    }
}
