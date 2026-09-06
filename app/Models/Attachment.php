<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'title',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'attachment_type',
        'task_key',
        'sort_order',
        'uploaded_by',
    ];

    protected static function booted(): void
    {
        static::creating(function (Attachment $attachment) {
            if (! is_null($attachment->sort_order)) {
                return;
            }

            $next = static::where('attachable_type', $attachment->attachable_type)
                ->where('attachable_id', $attachment->attachable_id)
                ->max('sort_order');

            $attachment->sort_order = is_null($next) ? 0 : $next + 1;
        });
    }

    public function attachable()
    {
        return $this->morphTo();
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    public function humanFileSize(): string
    {
        $bytes = $this->file_size ?? 0;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }
}
