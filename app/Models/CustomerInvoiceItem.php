<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInvoiceItem extends Model
{
    protected $fillable = [
        'customer_invoice_id',
        'is_fixed_charge',
        'supplier_id',
        'inspection_type_id',
        'po_invoice_no',
        'item_date',
        'amount',
    ];

    protected $casts = [
        'is_fixed_charge' => 'boolean',
        'item_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(CustomerInvoice::class, 'customer_invoice_id');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }
}
