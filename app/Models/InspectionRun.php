<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionRun extends Model
{
    protected $fillable = ['inspection_id', 'inspection_type_id', 'remarks'];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class);
    }

    public function inspectionType()
    {
        return $this->belongsTo(InspectionType::class);
    }

    public function results()
    {
        return $this->hasMany(InspectionResult::class);
    }

    public function sampleMovement()
    {
        return $this->hasOne(SampleMovement::class);
    }
}
