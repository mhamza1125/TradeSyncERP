<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            // Configurable invoice number format, e.g. "INV-{year}-{id:5}".
            // Defaults to the pattern that reproduces the legacy hardcoded
            // format (INV-2026-00013) so existing installations see no
            // change until an admin edits it in Company Settings. Kept as a
            // literal here (not App\Services\Finance\InvoiceNumberService::DEFAULT_PATTERN)
            // so this migration stays stable even if that constant changes later.
            $table->string('invoice_number_pattern')
                ->default('INV-{year}-{id:5}')
                ->after('default_terms');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn('invoice_number_pattern');
        });
    }
};
