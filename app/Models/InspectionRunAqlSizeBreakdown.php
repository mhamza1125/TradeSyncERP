<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionRunAqlSizeBreakdown extends Model
{
    protected $fillable = [
        'run_aql_id',
        'size_label',
        'order_qty',
        'checked_qty',
        'error_qty',
        'sort_order',
    ];

    protected $casts = [
        'order_qty' => 'integer',
        'checked_qty' => 'integer',
        'error_qty' => 'integer',
    ];

    public function runAql()
    {
        return $this->belongsTo(InspectionRunAql::class, 'run_aql_id');
    }
}
