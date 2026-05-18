<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleSize extends Model
{
    protected $fillable = ['name'];

    public function variations()
    {
        return $this->hasMany(SampleVariation::class, 'size_id');
    }
}
