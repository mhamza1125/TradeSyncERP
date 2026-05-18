<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleColor extends Model
{
    protected $fillable = ['name'];

    public function variations()
    {
        return $this->hasMany(SampleVariation::class, 'color_id');
    }
}
