<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stock Inward / Production Additions:
     * Directly fulfills requirement: "som time they will add jewellery in stock from production,
     * you don have to keep track of production."
     * Handles adding finished goods into stock (from workshop/artisan production, initial stock, or adjustments)
     * without overhead of manufacturing pipeline tracking.
     */
    public function up(): void
    {
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique(); // e.g. STK-2026-0001
            $table->date('entry_date')->index();
            $table->enum('entry_type', ['production_inward', 'opening_stock', 'surplus_adjustment', 'damage_writeoff'])->default('production_inward')->index();
            $table->foreignId('artisan_id')->nullable()->constrained('parties')->nullOnDelete(); // Optional artisan/karigar
            $table->integer('total_items_count')->default(0);

            // Summary weights
            $table->decimal('total_gross_weight', 10, 3)->default(0.000);
            $table->decimal('total_stone_weight', 10, 3)->default(0.000);
            $table->decimal('total_net_weight', 10, 3)->default(0.000);
            $table->decimal('total_fine_gold_weight', 10, 3)->default(0.000);
            $table->decimal('total_estimated_valuation', 14, 2)->default(0.00);

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('stock_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_entry_id')->constrained('stock_entries')->cascadeOnDelete();
            $table->foreignId('jewellery_item_id')->nullable()->constrained('jewellery_items')->nullOnDelete();
            $table->foreignId('category_id')->constrained('jewellery_categories');
            $table->foreignId('purity_id')->constrained('purities');
            $table->string('item_name', 200);
            $table->string('tag_code', 50)->nullable();
            $table->string('huid', 20)->nullable();

            $table->decimal('gross_weight', 10, 3);
            $table->decimal('stone_weight', 10, 3)->default(0.000);
            $table->decimal('net_weight', 10, 3);
            $table->decimal('fine_weight', 10, 3);

            $table->decimal('estimated_rate_per_gram', 12, 2)->default(0.00);
            $table->decimal('making_cost', 12, 2)->default(0.00);
            $table->decimal('stone_cost', 12, 2)->default(0.00);
            $table->decimal('total_cost', 14, 2)->default(0.00);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_entry_items');
        Schema::dropIfExists('stock_entries');
    }
};
