<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleVariation extends Model
{
    protected $fillable = ['sample_id', 'color_id', 'size_id', 'quantity'];

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }

    public function color()
    {
        return $this->belongsTo(SampleColor::class, 'color_id');
    }

    public function size()
    {
        return $this->belongsTo(SampleSize::class, 'size_id');
    }
}
