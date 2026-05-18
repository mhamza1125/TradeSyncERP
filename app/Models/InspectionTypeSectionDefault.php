<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionTypeSectionDefault extends Model
{
    protected $fillable = [
        'inspection_type_id',
        'inspection_section_id',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }

    public function section()
    {
        return $this->belongsTo(InspectionSection::class, 'inspection_section_id');
    }
}
