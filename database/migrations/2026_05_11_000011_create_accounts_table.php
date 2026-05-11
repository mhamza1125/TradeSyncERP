<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name');
            $table->enum('account_type', ['Cash', 'Bank', 'Ledger']);
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->string('currency', 10)->default('PKR');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
