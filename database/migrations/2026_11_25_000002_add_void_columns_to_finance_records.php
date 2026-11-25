<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Audit F-10: finance records are voided (kept, excluded from balances) instead of deleted.
 * Additive only: three nullable columns on each table; no existing row is changed.
 */
return new class extends Migration
{
    private const TABLES = ['transactions', 'expenses', 'customer_payments'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->timestamp('voided_at')->nullable();
                $t->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
                $t->string('void_reason', 500)->nullable();
                $t->index('voided_at');
            });
        }
    }

    /** Refuses while any row is voided: dropping the columns would silently un-void it. */
    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasColumn($table, 'voided_at') && DB::table($table)->whereNotNull('voided_at')->exists()) {
                throw new RuntimeException("Cannot roll back: {$table} has voided rows.");
            }
        }

        foreach (self::TABLES as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropForeign(['voided_by']);
                $t->dropIndex(['voided_at']);
                $t->dropColumn(['voided_at', 'voided_by', 'void_reason']);
            });
        }
    }
};
