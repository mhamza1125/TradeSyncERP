<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Single-row, application-wide company profile. Used to populate invoices,
 * payment receipts, PDF exports, and other official documents so that no
 * company detail is hard-coded elsewhere in the app.
 *
 * Always access via CompanySetting::current() — never query the table
 * directly — so the singleton row (id = 1) is guaranteed to exist.
 */
class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'tagline',
        'logo_path',
        'phone',
        'fax',
        'email',
        'website',
        'address',
        'city',
        'country',
        'postal_code',
        'registration_number',
        'ntn_number',
        'strn_number',
        'ceo_name',
        'contact_person_name',
        'contact_person_phone',
        'contact_person_email',
        'default_terms',
        'invoice_number_pattern',
    ];

    /**
     * Fetch the single company settings row, creating it with sensible
     * defaults on first access so the app never has to null-check it.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'company_name' => config('app.name', 'TradeSyncERP'),
        ]);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) {
            return null;
        }

        return Storage::url($this->logo_path);
    }

    /**
     * Absolute filesystem path to the logo, for use in DomPDF templates
     * (which require real paths, not public URLs). Falls back to null if
     * no logo has been uploaded or the file is missing on disk.
     */
    public function getLogoAbsolutePathAttribute(): ?string
    {
        if (! $this->logo_path || ! Storage::disk('public')->exists($this->logo_path)) {
            return null;
        }

        return Storage::disk('public')->path($this->logo_path);
    }
}
