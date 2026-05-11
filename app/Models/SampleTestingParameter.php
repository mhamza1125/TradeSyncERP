<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SampleTestingParameter extends Model
{
    protected $fillable = [
        'sample_id',
        'parameter_id',
        'requirement_standard',
        'remarks',
    ];

    public function sample()
    {
        return $this->belongsTo(Sample::class);
    }

    public function parameter()
    {
        return $this->belongsTo(TestingParameter::class, 'parameter_id');
    }

    public function inspectionResults()
    {
        return $this->hasMany(InspectionResult::class, 'sample_testing_parameter_id');
    }
}
