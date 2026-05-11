<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ProductCategory extends Model
{
    use LogsActivity;

    protected $fillable = [
        'category_name',
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

    public function testingParameters()
    {
        return $this->hasMany(TestingParameter::class, 'category_id');
    }

    public function samples()
    {
        return $this->hasMany(Sample::class, 'category_id');
    }
}
