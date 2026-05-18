<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    protected $fillable = ['defect_category_id', 'defect_name', 'corrective_action', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(DefectCategory::class, 'defect_category_id');
    }
}
