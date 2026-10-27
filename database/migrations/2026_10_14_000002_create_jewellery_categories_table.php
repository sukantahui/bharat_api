<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Jewellery Categories:
     * Classification of gold jewellery items (e.g., Ring, Necklace, Bangle, Chain, Earring, Coin, Bar).
     * Includes default HSN code (7113 for gold articles/jewellery, 7108 for gold bullion/coins).
     */
    public function up(): void
    {
        Schema::create('jewellery_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name', 100);
            $table->string('category_code', 20)->unique();
            $table->string('default_hsn_code', 10)->default('7113');
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
        Schema::dropIfExists('jewellery_categories');
    }
};
