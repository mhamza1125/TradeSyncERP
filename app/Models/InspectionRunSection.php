<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\GuardsFinishedRun;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class InspectionRunSection extends Model
{
    use GuardsFinishedRun, LogsActivity;

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
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('sort_order')->orderBy('id');
    }

    public function defects()
    {
        return $this->hasMany(InspectionRunDefect::class)->orderBy('sort_order');
    }

    public function isComplete(): bool
    {
        return $this->status === 'complete';
    }

    public function owningRun(): ?InspectionRun
    {
        return InspectionRun::find($this->inspection_run_id);
    }

    /** Status and notes only; the section data JSON (and photos) are not copied into the log. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['inspection_run_id', 'inspection_section_id', 'status', 'notes'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
