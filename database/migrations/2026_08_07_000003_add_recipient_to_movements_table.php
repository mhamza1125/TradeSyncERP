<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            // Who the samples were physically handed to (distinct from the "Assigned
            // Employees" pivot, which tracks internal staff/custody). Stored as a
            // simple type string ('Employee'|'Supplier'|'Customer') + id, resolved
            // manually on the model — matching the convention already used by the
            // legacy SampleMovement::moved_by_type / assigned_to_type columns.
            $table->string('recipient_type')->nullable()->after('inspection_run_id');
            $table->unsignedBigInteger('recipient_id')->nullable()->after('recipient_type');
        });
    }

    public function down(): void
    {
        Schema::table('movements', function (Blueprint $table) {
            $table->dropColumn(['recipient_type', 'recipient_id']);
        });
    }
};
