<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single-row settings table. The app always reads/writes the row with id = 1
     * via CompanySetting::current() — see app/Models/CompanySetting.php.
     */
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();

            // General Information
            $table->string('company_name')->default('TradeSyncERP');
            $table->string('tagline')->nullable();
            $table->string('logo_path')->nullable();

            // Contact Information
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();

            // Tax & Registration
            $table->string('registration_number')->nullable(); // CNIC / Company Registration No.
            $table->string('ntn_number')->nullable();
            $table->string('strn_number')->nullable();

            // Management
            $table->string('ceo_name')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('contact_person_email')->nullable();

            // Document defaults (reused across invoices/receipts/reports)
            $table->text('default_terms')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
