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

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function runs()
    {
        return $this->hasManyThrough(InspectionRun::class, Inspection::class);
    }

    public function sectionDefaults()
    {
        return $this->hasMany(InspectionTypeSectionDefault::class)
            ->orderBy('sort_order');
    }

    /** Global sections only (category_id IS NULL). */
    public function globalSectionDefaults()
    {
        return $this->hasMany(InspectionTypeSectionDefault::class)
            ->whereNull('category_id')
            ->orderBy('sort_order');
    }

    /** Section defaults for a specific product category. */
    public function sectionDefaultsForCategory(int $categoryId)
    {
        return $this->hasMany(InspectionTypeSectionDefault::class)
            ->where('category_id', $categoryId)
            ->orderBy('sort_order');
    }

    /**
     * Return the merged, deduplicated collection of active InspectionSection
     * records that apply to this type for the given $categoryId:
     *   • all global (NULL) defaults
     *   • all category-specific defaults whose category_id == $categoryId
     */
    public function resolvedSectionsForCategory(?int $categoryId = null): \Illuminate\Support\Collection
    {
        $query = InspectionTypeSectionDefault::with('section')
            ->whereHas('section', fn ($q) => $q->where('is_active', true))
            ->where('inspection_type_id', $this->id)
            ->where(function ($q) use ($categoryId) {
                $q->whereNull('category_id');
                if ($categoryId) {
                    $q->orWhere('category_id', $categoryId);
                }
            })
            ->orderBy('sort_order');

        return $query->get()
            ->unique('inspection_section_id')
            ->values();
    }

    public function defaultSections()
    {
        return $this->belongsToMany(
            InspectionSection::class,
            'inspection_type_section_defaults',
            'inspection_type_id',
            'inspection_section_id'
        )->withPivot('sort_order', 'is_required', 'category_id')
         ->orderBy('inspection_type_section_defaults.sort_order');
    }
}
