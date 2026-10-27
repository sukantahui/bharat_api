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
        Schema::table('business_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('business_profiles', 'website')) {
                $table->string('website', 255)->nullable()->after('email');
            }
            if (!Schema::hasColumn('business_profiles', 'bank_name')) {
                $table->string('bank_name', 255)->nullable()->after('terms_and_conditions');
            }
            if (!Schema::hasColumn('business_profiles', 'bank_account_number')) {
                $table->string('bank_account_number', 50)->nullable()->after('bank_name');
            }
            if (!Schema::hasColumn('business_profiles', 'bank_ifsc')) {
                $table->string('bank_ifsc', 20)->nullable()->after('bank_account_number');
            }
            if (!Schema::hasColumn('business_profiles', 'bank_branch')) {
                $table->string('bank_branch', 255)->nullable()->after('bank_ifsc');
            }
            if (!Schema::hasColumn('business_profiles', 'upi_id')) {
                $table->string('upi_id', 100)->nullable()->after('bank_branch');
            }
            if (!Schema::hasColumn('business_profiles', 'traded_metals')) {
                $table->string('traded_metals', 20)->default('both')->after('upi_id');
            }
            if (!Schema::hasColumn('business_profiles', 'state_name')) {
                $table->string('state_name', 100)->nullable()->after('state_code');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'website',
                'bank_name',
                'bank_account_number',
                'bank_ifsc',
                'bank_branch',
                'upi_id',
                'traded_metals',
                'state_name',
            ]);
        });
    }
};
