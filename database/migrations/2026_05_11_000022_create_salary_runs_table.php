<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_runs', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7)->unique()->comment('Format: YYYY-MM');
            $table->foreignId('account_id')->constrained('accounts');
            $table->decimal('total_net_payable', 15, 2)->default(0);
            $table->enum('status', ['Draft', 'Paid'])->default('Draft');
            $table->date('payment_date')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('processed_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_runs');
    }
};
