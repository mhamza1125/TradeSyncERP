<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovementItem extends Model
{
    protected $fillable = [
        'movement_id',
        'sample_id',
        'sample_variation_id',
        'quantity',
        'actual_return_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'actual_return_date' => 'date',
    ];

    public function movement()
    {
        return $this->belongsTo(Movement::class);
    }

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }

    public function variation()
    {
        return $this->belongsTo(SampleVariation::class, 'sample_variation_id');
    }

    public function effectiveStatus(): string
    {
        return $this->status ?? $this->movement->status;
    }
}
