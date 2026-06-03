<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowanceType extends Model
{
    protected $fillable = ['name', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function lineAllowances()
    {
        return $this->hasMany(SalaryRunLineAllowance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
