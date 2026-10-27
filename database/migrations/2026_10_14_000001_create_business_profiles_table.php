<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Business Profiles / Organization Settings:
     * Stores jewellery shop identity, GST registration status (or non-GST flag),
     * BIS Hallmark license, default prefixes, and place of business state.
     */
    public function up(): void
    {
        Schema::create('business_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 255);
            $table->string('trade_name', 255)->nullable();
            $table->string('legal_name', 255)->nullable();
            $table->string('tagline', 255)->nullable();

            // GST Configuration (Handles shops with or without GST accounts)
            $table->boolean('is_gst_registered')->default(false)->index();
            $table->enum('gst_scheme', ['regular', 'composition', 'unregistered'])->default('unregistered');
            $table->string('gstin', 15)->nullable()->index();
            $table->string('pan_number', 10)->nullable()->index();
            $table->string('hallmark_license_no', 50)->nullable(); // BIS Hallmark License

            // Contact & Physical Location
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address_line1', 255)->nullable();
            $table->string('address_line2', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->integer('state_code')->nullable(); // e.g. 19 for West Bengal, 27 for Maharashtra
            $table->string('pincode', 10)->nullable();

            // Default Invoicing & System Configuration
            $table->string('currency_symbol', 10)->default('₹');
            $table->string('invoice_prefix', 20)->default('INV-');
            $table->string('purchase_prefix', 20)->default('PUR-');
            $table->string('stock_entry_prefix', 20)->default('STK-');
            $table->string('voucher_prefix', 20)->default('VCH-');
            $table->date('financial_year_start')->nullable();
            $table->text('terms_and_conditions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profiles');
    }
};
