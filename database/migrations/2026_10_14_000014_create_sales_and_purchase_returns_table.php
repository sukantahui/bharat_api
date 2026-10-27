<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Sales Returns (Credit Notes) & Purchase Returns (Debit Notes):
     * - Essential for Indian GST Compliance: GSTR-1 Table 9B (Credit/Debit Notes)
     * - Handles customer returns/exchanges after invoice issuance with stock restock/scrap actions
     * - Handles vendor procurement returns (Debit Notes) to reverse ITC and adjust vendor payables.
     */
    public function up(): void
    {
        // -------------------------------------------------------------
        // 1. SALES RETURNS (CREDIT NOTES ISSUED TO CUSTOMERS)
        // -------------------------------------------------------------
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_no', 50)->unique(); // e.g. CRN-2026-0001
            $table->date('credit_note_date')->index();
            $table->foreignId('sale_id')->constrained('sales');
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();

            // Customer Identifiers
            $table->string('customer_name', 200);
            $table->string('customer_mobile', 20)->nullable();
            $table->string('customer_gstin', 15)->nullable()->index(); // For B2B Credit Notes (GSTR-1 Table 9B CDNR)

            // Original Invoice linkage (Statutory GSTR-1 requirement)
            $table->string('original_invoice_no', 50)->index();
            $table->date('original_invoice_date');

            // Place of Supply
            $table->foreignId('place_of_supply_state_id')->nullable()->constrained('states')->nullOnDelete();
            $table->integer('place_of_supply_state_code')->nullable();

            // Taxation details
            $table->boolean('is_gst_applicable')->default(true)->index();
            $table->enum('tax_type', ['intra_state', 'inter_state', 'non_gst'])->default('intra_state')->index();

            // Returned Weights
            $table->decimal('total_gross_weight', 10, 3)->default(0.000);
            $table->decimal('total_stone_weight', 10, 3)->default(0.000);
            $table->decimal('total_net_weight', 10, 3)->default(0.000);
            $table->decimal('total_fine_gold_weight', 10, 3)->default(0.000);

            // Financial & Tax reversal amounts
            $table->decimal('taxable_amount', 14, 2)->default(0.00);
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_rate', 5, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_tax_amount', 12, 2)->default(0.00);
            $table->decimal('round_off', 8, 2)->default(0.00);
            $table->decimal('total_credit_amount', 14, 2)->default(0.00); // Total refund / credit value

            // Settlement & Return categorization
            $table->enum('refund_mode', ['store_credit_balance', 'cash_refund', 'bank_refund', 'replacement_exchange'])->default('store_credit_balance');
            $table->foreignId('refund_payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->enum('return_reason', ['exchange', 'defective', 'size_issue', 'customer_return', 'other'])->default('exchange');

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sales_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignId('sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->foreignId('jewellery_item_id')->nullable()->constrained('jewellery_items')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('jewellery_categories')->nullOnDelete();
            $table->foreignId('purity_id')->constrained('purities');

            $table->string('item_name', 255);
            $table->string('tag_code', 50)->nullable();
            $table->string('hsn_code', 10)->default('7113');

            // Weight components
            $table->decimal('gross_weight', 10, 3);
            $table->decimal('stone_weight', 10, 3)->default(0.000);
            $table->decimal('net_weight', 10, 3);
            $table->decimal('fine_gold_weight', 10, 3);

            // Rate and refund amounts
            $table->decimal('rate_per_gram', 12, 2);
            $table->decimal('gold_amount', 14, 2);
            $table->decimal('making_charges_refunded', 12, 2)->default(0.00);
            $table->decimal('stone_charges_refunded', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 14, 2);

            $table->decimal('gst_rate', 5, 2)->default(3.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2);

            // Inventory restocking action
            $table->enum('stock_action', ['restocked_as_original', 'converted_to_scrap', 'sent_for_repair', 'none'])->default('restocked_as_original');
            $table->timestamps();
        });

        // -------------------------------------------------------------
        // 2. PURCHASE RETURNS (DEBIT NOTES ISSUED TO VENDORS)
        // -------------------------------------------------------------
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->id();
            $table->string('debit_note_no', 50)->unique(); // e.g. DBN-2026-0001
            $table->date('debit_note_date')->index();
            $table->foreignId('purchase_id')->constrained('purchases');
            $table->foreignId('party_id')->constrained('parties'); // Vendor

            $table->string('original_bill_no', 50)->nullable();
            $table->date('original_bill_date')->nullable();

            $table->boolean('is_gst_applicable')->default(true)->index();
            $table->enum('tax_type', ['intra_state', 'inter_state', 'non_gst'])->default('intra_state')->index();

            // Returned Weights
            $table->decimal('total_gross_weight', 10, 3)->default(0.000);
            $table->decimal('total_net_weight', 10, 3)->default(0.000);
            $table->decimal('total_fine_gold_weight', 10, 3)->default(0.000);

            // Taxable & Reversed amounts
            $table->decimal('taxable_amount', 14, 2)->default(0.00);
            $table->decimal('cgst_rate', 5, 2)->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->default(0.00);
            $table->decimal('sgst_rate', 5, 2)->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->default(0.00);
            $table->decimal('igst_rate', 5, 2)->default(0.00);
            $table->decimal('igst_amount', 12, 2)->default(0.00);
            $table->decimal('total_tax_amount', 12, 2)->default(0.00);
            $table->decimal('round_off', 8, 2)->default(0.00);
            $table->decimal('total_debit_amount', 14, 2)->default(0.00);

            $table->enum('settlement_mode', ['deducted_from_vendor_balance', 'bank_received', 'cash_received', 'replacement_pending'])->default('deducted_from_vendor_balance');
            $table->string('return_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('purchase_return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained('purchase_items')->nullOnDelete();
            $table->foreignId('jewellery_item_id')->nullable()->constrained('jewellery_items')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('jewellery_categories')->nullOnDelete();
            $table->foreignId('purity_id')->constrained('purities');

            $table->string('item_description', 255);
            $table->string('hsn_code', 10)->default('7113');

            $table->decimal('gross_weight', 10, 3);
            $table->decimal('stone_weight', 10, 3)->default(0.000);
            $table->decimal('net_weight', 10, 3);
            $table->decimal('fine_gold_weight', 10, 3);

            $table->decimal('rate_per_gram', 12, 2);
            $table->decimal('gold_amount', 14, 2);
            $table->decimal('making_charges', 12, 2)->default(0.00);
            $table->decimal('taxable_amount', 14, 2);

            $table->decimal('gst_rate', 5, 2)->default(3.00);
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
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
    }
};
