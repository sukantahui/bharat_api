<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Daily Gold Rates:
     * Maintains daily official selling and buying (buyback) metal rates per gram for each purity.
     * All sales and purchase transactions use or reference these rates.
     */
    public function up(): void
    {
        Schema::create('daily_gold_rates', function (Blueprint $table) {
            $table->id();
            $table->date('rate_date')->index();
            $table->foreignId('purity_id')->constrained('purities')->cascadeOnDelete();
            $table->enum('metal_type', ['gold', 'silver', 'platinum'])->default('gold');
            $table->decimal('selling_rate_per_gram', 12, 2); // Retail selling rate / gram
            $table->decimal('buying_rate_per_gram', 12, 2);  // Old gold buyback rate / gram
            $table->time('effective_time')->nullable();
            $table->string('notes', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['rate_date', 'purity_id', 'metal_type'], 'unique_daily_rate_per_purity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_gold_rates');
    }
};
