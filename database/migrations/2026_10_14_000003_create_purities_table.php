<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Purities:
     * Standard gold purities and fineness (e.g., 24K [99.9%], 22K [91.6% - 916 Hallmark], 18K [75.0% - 750 Hallmark], 14K [58.5% - 585 Hallmark]).
     * Essential for calculating fine gold weight (Net Weight * Purity %) and daily pricing.
     */
    public function up(): void
    {
        Schema::create('purities', function (Blueprint $table) {
            $table->id();
            $table->string('karat_name', 50); // e.g. "22 Karat (916 Hallmark)"
            $table->string('karat_code', 20)->unique(); // e.g. "22K", "24K", "18K", "14K"
            $table->decimal('purity_percentage', 6, 3); // e.g. 91.667 for 22K, 99.900 for 24K
            $table->string('hallmark_code', 20)->nullable(); // e.g. "916", "999", "750", "585"
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purities');
    }
};
