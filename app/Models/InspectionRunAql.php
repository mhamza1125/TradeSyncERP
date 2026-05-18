<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionRunAql extends Model
{
    protected $table = 'inspection_run_aql';

    protected $fillable = [
        'inspection_run_id',
        'lot_size', 'inspection_level', 'sample_size', 'code_letter',
        'aql_critical', 'aql_major', 'aql_minor',
        'ac_critical', 're_critical',
        'ac_major',    're_major',
        'ac_minor',    're_minor',
        'found_critical', 'found_major', 'found_minor',
        'verdict', 'notes',
    ];

    protected $casts = [
        'lot_size'       => 'integer',
        'sample_size'    => 'integer',
        'aql_critical'   => 'float',
        'aql_major'      => 'float',
        'aql_minor'      => 'float',
        'found_critical' => 'integer',
        'found_major'    => 'integer',
        'found_minor'    => 'integer',
    ];

    public function run()
    {
        return $this->belongsTo(InspectionRun::class, 'inspection_run_id');
    }

    public function calculateVerdict(): string
    {
        $fail =
            ($this->ac_critical !== null && $this->found_critical > $this->ac_critical) ||
            ($this->ac_major    !== null && $this->found_major    > $this->ac_major)    ||
            ($this->ac_minor    !== null && $this->found_minor    > $this->ac_minor);

        if ($this->found_critical === 0 && $this->found_major === 0 && $this->found_minor === 0) {
            return 'Pending';
        }

        return $fail ? 'Fail' : 'Pass';
    }
}
