<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionRun extends Model
{
    protected $fillable = [
        'inspection_id', 'sample_id',
        'run_number', 'verdict', 'remarks',
        'started_at', 'completed_at',
    ];

    protected $casts = [
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }


    public function runSections()
    {
        return $this->hasMany(InspectionRunSection::class)->orderBy('sort_order');
    }

    public function aql()
    {
        return $this->hasOne(InspectionRunAql::class, 'inspection_run_id');
    }

    public function sampleMovement()
    {
        return $this->hasOne(SampleMovement::class);
    }

    public function sampleMovements()
    {
        return $this->hasMany(SampleMovement::class);
    }

    public function movements()
    {
        return $this->hasMany(Movement::class);
    }

    public function hasSectionEnabled(string $slug): bool
    {
        return $this->runSections
            ->contains(fn($rs) => $rs->section?->slug === $slug);
    }

    public function getSectionData(string $slug): ?InspectionRunSection
    {
        return $this->runSections
            ->first(fn($rs) => $rs->section?->slug === $slug);
    }
}
