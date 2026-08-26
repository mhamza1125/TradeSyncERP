<?php

namespace App\Services\Finance;

use App\Models\CompanySetting;
use App\Models\CustomerInvoice;

/**
 * Formats customer invoice numbers from the pattern configured in Company
 * Settings (CompanySetting::invoice_number_pattern), instead of the format
 * being hard-coded in the controller.
 *
 * The numeric sequence itself is always derived from the next
 * customer_invoices.id (including soft-deleted rows), never re-derived from
 * the formatted string — so changing the pattern later never renumbers or
 * collides with invoices that were generated under an older pattern.
 */
class InvoiceNumberService
{
    /**
     * Reproduces the legacy hard-coded format (e.g. INV-2026-00013), used
     * whenever Company Settings has no pattern configured yet.
     */
    public const DEFAULT_PATTERN = 'INV-{year}-{id:5}';

    /**
     * Generate the next invoice number using the pattern currently
     * configured in Company Settings.
     */
    public function generateNext(): string
    {
        return $this->format($this->pattern(), $this->nextSequentialId());
    }

    /**
     * The pattern currently configured in Company Settings, falling back to
     * the default when unset/blank.
     */
    public function pattern(): string
    {
        return CompanySetting::current()->invoice_number_pattern ?: self::DEFAULT_PATTERN;
    }

    /**
     * The id the next created invoice is expected to receive.
     */
    public function nextSequentialId(): int
    {
        return (int) (CustomerInvoice::withTrashed()->max('id') ?? 0) + 1;
    }

    /**
     * Render a pattern for a given sequential id. Supported placeholders:
     *   {id}      - raw sequence number, e.g. 13
     *   {id:N}    - sequence number zero-padded to N digits, e.g. {id:5} → 00013
     *   {year}    - current 4-digit year, e.g. 2026
     *   {yy}      - current 2-digit year, e.g. 26
     *   {month}   - current 2-digit month, e.g. 08
     */
    public function format(string $pattern, int $id): string
    {
        $now = now();

        $formatted = preg_replace_callback('/\{id(?::(\d+))?\}/', function ($matches) use ($id) {
            return isset($matches[1])
                ? str_pad((string) $id, (int) $matches[1], '0', STR_PAD_LEFT)
                : (string) $id;
        }, $pattern);

        return strtr($formatted, [
            '{year}' => $now->format('Y'),
            '{yy}' => $now->format('y'),
            '{month}' => $now->format('m'),
        ]);
    }

    /**
     * Whether a pattern is safe to save: it must contain an {id} placeholder
     * (with or without padding), otherwise every generated invoice number
     * would collide against the unique invoice_number column.
     */
    public function hasIdPlaceholder(string $pattern): bool
    {
        return (bool) preg_match('/\{id(?::\d+)?\}/', $pattern);
    }
}
