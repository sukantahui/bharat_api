<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Parties (Customers, Vendors/Wholesalers, Karigars/Artisans):
     * Maintains counter-parties for purchases, sales, and payments.
     * Includes state/state_code for GST tax determination (CGST+SGST vs IGST),
     * GSTIN, PAN (mandatory for high-value transactions), and ledger balances.
     */
    public function up(): void
    {
        Schema::create('parties', function (Blueprint $table) {
            $table->id();
            $table->enum('party_type', ['customer', 'vendor', 'artisan', 'both'])->default('customer')->index();
            $table->string('name', 200);
            $table->string('trade_name', 200)->nullable();
            $table->string('mobile', 20)->nullable()->index();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->integer('state_code')->nullable(); // For GST place of supply checking
            $table->string('pincode', 10)->nullable();

            // Taxation identifiers
            $table->enum('gst_type', ['unregistered', 'registered_regular', 'registered_composition', 'consumer'])->default('unregistered')->index();
            $table->string('gstin', 15)->nullable()->index();
            $table->string('pan_number', 10)->nullable()->index(); // Mandatory for transactions >= ₹2,00,000 cash
            $table->string('aadhaar_number', 20)->nullable();

            // Financial & Metal Ledger Balances
            $table->decimal('opening_balance', 14, 2)->default(0.00); // Currency balance
            $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');
            $table->decimal('current_balance', 14, 2)->default(0.00);
            $table->decimal('metal_opening_balance_grams', 10, 3)->default(0.000); // Pure gold weight owed / owe
            $table->decimal('metal_current_balance_grams', 10, 3)->default(0.000);

            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parties');
    }
};
