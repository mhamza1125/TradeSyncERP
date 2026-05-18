<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefectCategory extends Model
{
    protected $fillable = ['name', 'code', 'description', 'color', 'sort_order'];

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }
}
