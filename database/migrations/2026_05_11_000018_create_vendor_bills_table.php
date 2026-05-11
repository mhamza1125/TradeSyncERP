<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('vendors');
            $table->string('bill_number')->unique();
            $table->date('bill_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->enum('status', ['Unpaid', 'Paid', 'Partial', 'Overdue'])->default('Unpaid');
            $table->text('remarks')->nullable();
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bills');
    }
};
