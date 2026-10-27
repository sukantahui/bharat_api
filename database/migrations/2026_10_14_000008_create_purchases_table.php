<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Purchases & Purchase Items:
     * Manages gold jewellery procurement from wholesale vendors and old gold buybacks.
     * Supports both GST-compliant invoices and non-GST purchases.
     * Provides tax breakdowns (CGST, SGST, IGST) and ITC eligibility for GST reporting.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_invoice_no', 50)->unique(); // Internal sequence e.g. PUR-2026-0001
            $table->date('purchase_date')->index();
            $table->string('vendor_bill_no', 50)->nullable();
            $table->date('vendor_bill_date')->nullable();
            $table->foreignId('party_id')->constrained('parties'); // Vendor or customer
            $table->enum('purchase_type', ['vendor_jewellery', 'bullion_raw_gold', 'customer_old_gold_buyback'])->default('vendor_jewellery')->index();

            // Taxation parameters (accommodates shops with or without GST)
            $table->boolean('is_gst_applicable')->default(true)->index();
            $table->enum('tax_type', ['intra_state', 'inter_state', 'non_gst'])->default('intra_state')->index();

            // Summary weights
            $table->decimal('total_gross_weight', 10, 3)->default(0.000);
            $table->decimal('total_net_weight', 10, 3)->default(0.000);
            $table->decimal('total_fine_gold_weight', 10, 3)->default(0.000);

            // Financial & Tax Amounts
            $table->decimal('total_gold_amount', 14, 2)->default(0.00);
            $table->decimal('total_making_charges', 12, 2)->default(0.00);
            $table->decimal('total_stone_charges', 12, 2)->default(0.00);
            $table->decimal('other_charges', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 14, 2)->default(0.00);

            // GST Breakup
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_rate', 5, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_tax_amount', 12, 2)->default(0.00);

            $table->decimal('round_off', 8, 2)->default(0.00);
            $table->decimal('grand_total', 14, 2)->default(0.00);
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->decimal('balance_amount', 14, 2)->default(0.00);
            $table->enum('payment_status', ['unpaid', 'partially_paid', 'paid'])->default('unpaid')->index();

            // Input Tax Credit (ITC) categorization for GSTR-3B
            $table->enum('itc_eligibility', ['inputs', 'capital_goods', 'input_services', 'ineligible'])->default('inputs');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('jewellery_item_id')->nullable()->constrained('jewellery_items')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('jewellery_categories')->nullOnDelete();
            $table->foreignId('purity_id')->constrained('purities');
            $table->string('item_description', 255);
            $table->string('hsn_code', 10)->default('7113');

            // Weight components
            $table->decimal('gross_weight', 10, 3);
            $table->decimal('stone_weight', 10, 3)->default(0.000);
            $table->decimal('net_weight', 10, 3);
            $table->decimal('purity_percentage', 6, 3);
            $table->decimal('fine_gold_weight', 10, 3);

            // Rate and valuation
            $table->decimal('rate_per_gram', 12, 2);
            $table->decimal('gold_amount', 14, 2);
            $table->decimal('making_charges', 12, 2)->default(0.00);
            $table->decimal('stone_charges', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 14, 2);

            // GST fields
            $table->decimal('gst_rate', 5, 2)->default(3.00); // 3% on gold jewellery
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};
