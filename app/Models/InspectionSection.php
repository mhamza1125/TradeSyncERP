<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionSection extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'section_type',
        'icon', 'default_data', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'default_data' => 'array',
        'is_active'    => 'boolean',
    ];

    public function typeDefaults()
    {
        return $this->hasMany(InspectionTypeSectionDefault::class);
    }

    public function runSections()
    {
        return $this->hasMany(InspectionRunSection::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
