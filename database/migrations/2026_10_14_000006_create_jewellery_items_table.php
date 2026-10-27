<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Jewellery Items (Individual Inventory / Stock):
     * Tracks individual tagged gold jewellery pieces (or bulk batches).
     * Includes gross weight, stone weight, net gold weight, fine gold weight,
     * making charge rates, HSN code, HUID hallmark, and stock lifecycle.
     */
    public function up(): void
    {
        Schema::create('jewellery_items', function (Blueprint $table) {
            $table->id();
            $table->string('tag_code', 50)->unique(); // Unique tag/barcode/SKU (e.g. TAG-2026-0001)
            $table->string('item_name', 200);
            $table->foreignId('category_id')->constrained('jewellery_categories');
            $table->foreignId('purity_id')->constrained('purities');
            $table->string('hsn_code', 10)->default('7113')->index();

            // Weight attributes in grams (3 decimal places precision)
            $table->decimal('gross_weight', 10, 3); // Total physical weight
            $table->decimal('stone_weight', 10, 3)->default(0.000); // Stones, beads, enamels
            $table->decimal('net_gold_weight', 10, 3); // gross_weight - stone_weight
            $table->decimal('fine_gold_weight', 10, 3); // net_gold_weight * purity_percentage

            // Valuation & Pricing components
            $table->decimal('stone_value', 12, 2)->default(0.00); // Gemstone / diamond / bead value
            $table->enum('making_charge_type', ['per_gram', 'percentage', 'fixed'])->default('per_gram');
            $table->decimal('making_charge_rate', 10, 2)->default(0.00); // e.g. ₹650/g or 12%
            $table->decimal('purchase_cost', 14, 2)->default(0.00);
            $table->decimal('selling_min_rate_per_gram', 12, 2)->nullable();

            // Compliance & Identification
            $table->string('huid', 20)->nullable()->index(); // Hallmark Unique Identification (6-char BIS alphanumeric)

            // Stock Origin & Status
            $table->enum('source_type', ['purchase', 'production', 'opening_stock', 'old_gold_exchange'])->default('purchase')->index();
            $table->unsignedBigInteger('source_reference_id')->nullable()->index(); // Linked purchase_item_id or stock_entry_item_id
            $table->enum('status', ['in_stock', 'sold', 'returned_to_vendor', 'damaged'])->default('in_stock')->index();
            $table->date('sold_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jewellery_items');
    }
};
