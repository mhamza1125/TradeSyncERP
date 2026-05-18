<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionRunSection extends Model
{
    protected $fillable = [
        'inspection_run_id',
        'inspection_section_id',
        'sort_order',
        'data',
        'status',
        'notes',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function run()
    {
        return $this->belongsTo(InspectionRun::class, 'inspection_run_id');
    }

    public function section()
    {
        return $this->belongsTo(InspectionSection::class, 'inspection_section_id');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }
}
