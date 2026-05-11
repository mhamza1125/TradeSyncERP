<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_bill_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_bill_id')->constrained('vendor_bills')->cascadeOnDelete();
            $table->foreignId('inspection_id')->constrained('inspections')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['vendor_bill_id', 'inspection_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bill_inspections');
    }
};
