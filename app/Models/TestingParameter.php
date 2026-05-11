<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class TestingParameter extends Model
{
    use LogsActivity;

    protected $table = 'testing_parameters_master';

    protected $fillable = [
        'category_id',
        'parameter_name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function sampleTestingParameters()
    {
        return $this->hasMany(SampleTestingParameter::class, 'parameter_id');
    }
}
