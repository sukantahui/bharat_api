<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name',100)->unique(true);
            $table->string('employee_short_name',100)->unique(true)->nullable(true);
            $table->string('mobile',50);
            $table->string('email',200)->nullable(true);
            $table->foreignId('department_id')->constrained('departments')->restrictOnDelete();
            $table->foreignId('designation_id')->constrained('designations')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
