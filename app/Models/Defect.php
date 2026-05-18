<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    protected $fillable = ['defect_name', 'corrective_action', 'status'];

    protected $casts = ['status' => 'boolean'];
}
