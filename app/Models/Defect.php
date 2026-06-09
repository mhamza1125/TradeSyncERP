<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defect extends Model
{
    protected $fillable = ['defect_name', 'severity', 'corrective_action', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function getSeverityLabelAttribute(): string
    {
        return match($this->severity) {
            'critical'   => 'Critical',
            'major'      => 'Major',
            'minor'      => 'Minor',
            'functional' => 'Functional',
            default      => 'Unknown',
        };
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical'   => 'danger',
            'major'      => 'warning',
            'minor'      => 'info',
            'functional' => 'secondary',
            default      => 'secondary',
        };
    }
}
