<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionType extends Model
{
    use LogsActivity;

    protected $fillable = ['name', 'description', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function runs()
    {
        return $this->hasMany(InspectionRun::class);
    }

    public function sectionDefaults()
    {
        return $this->hasMany(InspectionTypeSectionDefault::class)
            ->orderBy('sort_order');
    }

    public function defaultSections()
    {
        return $this->belongsToMany(
            InspectionSection::class,
            'inspection_type_section_defaults',
            'inspection_type_id',
            'inspection_section_id'
        )->withPivot('sort_order', 'is_required')
         ->orderBy('inspection_type_section_defaults.sort_order');
    }
}
