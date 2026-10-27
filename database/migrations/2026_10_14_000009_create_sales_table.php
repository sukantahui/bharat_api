<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Sales, Sale Items, and Old Gold Exchanges:
     * Manages gold jewellery sales invoices with fine weight, daily gold rate,
     * making charges, stone charges, BIS hallmarking fee, and GST calculations.
     * Supports old gold exchange trade-in deduction and non-GST sales for unregistered shops.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique(); // e.g. INV-2026-0001
            $table->date('invoice_date')->index();
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete(); // Nullable for anonymous retail walk-in
            $table->string('customer_name', 200);
            $table->string('customer_mobile', 20)->nullable()->index();
            $table->string('customer_email', 150)->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_gstin', 15)->nullable()->index(); // For B2B Invoices (GSTR-1 Table 4)
            $table->string('customer_pan', 10)->nullable()->index();   // Mandatory for cash receipts >= ₹2 Lakhs

            // Place of Supply (Determines CGST+SGST vs IGST)
            $table->foreignId('place_of_supply_state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->integer('place_of_supply_state_code')->nullable();

            // Invoicing & Tax Mode
            $table->enum('invoice_type', ['tax_invoice', 'bill_of_supply', 'cash_memo'])->default('tax_invoice');
            $table->boolean('is_gst_applicable')->default(true)->index(); // Accommodates shops without GST accounts
            $table->enum('tax_type', ['intra_state', 'inter_state', 'non_gst'])->default('intra_state')->index();

            // Summary Weights
            $table->decimal('total_gross_weight', 10, 3)->default(0.000);
            $table->decimal('total_stone_weight', 10, 3)->default(0.000);
            $table->decimal('total_net_weight', 10, 3)->default(0.000);
            $table->decimal('total_fine_gold_weight', 10, 3)->default(0.000);

            // Financial & Pricing Totals
            $table->decimal('total_gold_amount', 14, 2)->default(0.00);
            $table->decimal('total_making_charges', 12, 2)->default(0.00);
            $table->decimal('total_stone_charges', 12, 2)->default(0.00);
            $table->decimal('hallmarking_charges', 10, 2)->default(0.00);
            $table->decimal('other_charges', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 14, 2)->default(0.00);

            // Tax Amounts (3% total for gold in India: 1.5% CGST + 1.5% SGST, or 3% IGST)
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_rate', 5, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_tax_amount', 12, 2)->default(0.00);

            // Customer Trade-In / Old Gold Exchange Value Deducted
            $table->decimal('old_gold_exchange_amount', 14, 2)->default(0.00);

            $table->decimal('round_off', 8, 2)->default(0.00);
            $table->decimal('grand_total', 14, 2)->default(0.00); // Net invoice total payable
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->decimal('balance_amount', 14, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'partially_paid', 'unpaid'])->default('unpaid')->index();

            $table->boolean('is_cancelled')->default(false)->index();
            $table->string('cancellation_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('jewellery_item_id')->nullable()->constrained('jewellery_items')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('jewellery_categories')->nullOnDelete();
            $table->foreignId('purity_id')->constrained('purities');
            $table->string('item_name', 255);
            $table->string('tag_code', 50)->nullable();
            $table->string('huid', 20)->nullable();
            $table->string('hsn_code', 10)->default('7113');

            // Weight components in grams
            $table->decimal('gross_weight', 10, 3);
            $table->decimal('stone_weight', 10, 3)->default(0.000);
            $table->decimal('net_weight', 10, 3);
            $table->decimal('fine_gold_weight', 10, 3);

            // Pricing formula: Gold Value + Making Charges + Stone Charges + Hallmarking
            $table->decimal('gold_rate_per_gram', 12, 2);
            $table->decimal('gold_amount', 14, 2);
            $table->enum('making_charge_type', ['per_gram', 'percentage', 'fixed'])->default('per_gram');
            $table->decimal('making_charge_rate', 10, 2)->default(0.00);
            $table->decimal('making_charges', 12, 2)->default(0.00);
            $table->decimal('stone_charges', 12, 2)->default(0.00);
            $table->decimal('hallmarking_charge', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 14, 2);

            // GST on item
            $table->decimal('gst_rate', 5, 2)->default(3.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2);
            $table->timestamps();
        });

        Schema::create('sale_old_gold_exchanges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('item_description', 255); // e.g. "Old 22K Broken Chain"
            $table->decimal('gross_weight', 10, 3);
            $table->decimal('dust_stone_weight', 10, 3)->default(0.000);
            $table->decimal('net_weight', 10, 3);
            $table->decimal('purity_percentage', 6, 3); // Tested purity %
            $table->decimal('fine_gold_weight', 10, 3);
            $table->decimal('buying_rate_per_gram', 12, 2);
            $table->decimal('total_exchange_value', 14, 2);
            $table->string('remarks', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_old_gold_exchanges');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
