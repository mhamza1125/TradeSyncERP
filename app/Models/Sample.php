<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Sample extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'sample_code',
        'category_id',
        'customer_id',
        'supplier_id',
        'received_by',
        'product_name',
        'article',
        'sample_reference',
        'physical_location',
        'source',
        'rack',
        'position',
        'main_image',
        'receive_date',
        'priority_level',
        'alert_days',
        'status',
        'remarks',
    ];

    protected $casts = [
        'receive_date' => 'date',
        'status'       => 'string',
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

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function receivedBy()
    {
        return $this->belongsTo(Employee::class, 'received_by');
    }

    public function variations()
    {
        return $this->hasMany(SampleVariation::class);
    }


    public function movements()
    {
        return $this->hasMany(SampleMovement::class);
    }

    public function movementItems()
    {
        return $this->hasMany(MovementItem::class);
    }

    public function computedStatus(): string
    {
        $hasOpenMovement = $this->movementItems()
            ->whereHas('movement', fn ($q) => $q->where('status', 'Issued'))
            ->exists();

        return $hasOpenMovement ? 'In Testing' : 'Received';
    }

    public static function syncStatusFromMovements(array $sampleIds): void
    {
        foreach (array_unique(array_filter($sampleIds)) as $sampleId) {
            $sample = static::find($sampleId);
            if (!$sample) {
                continue;
            }
            $hasOpen = $sample->movementItems()
                ->whereHas('movement', fn ($q) => $q->where('status', 'Issued'))
                ->exists();
            $sample->updateQuietly(['status' => $hasOpen ? 'In Testing' : 'Received']);
        }
    }

    public function runs()
    {
        return $this->hasMany(InspectionRun::class);
    }

    public function inspections()
    {
        return $this->belongsToMany(
            Inspection::class,
            'inspection_runs',
            'sample_id',
            'inspection_id'
        )->distinct();
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function mainImage(): ?Attachment
    {
        return $this->attachments()->where('attachment_type', 'main_image')->latest()->first();
    }

    public function galleryImages()
    {
        return $this->attachments()->where('attachment_type', 'gallery');
    }

    public function isOverdue(): bool
    {
        return !in_array($this->status, ['Completed', 'Returned'])
            && $this->receive_date->addDays($this->alert_days)->isPast();
    }
}
