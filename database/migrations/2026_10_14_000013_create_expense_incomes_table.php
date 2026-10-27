<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Operational Income & Expenditure:
     * Fast, streamlined day-to-day logging for shop managers:
     * - Expenses: Shop rent, staff salaries, electricity bills, refreshments, maintenance, security.
     * - Income: Old gold scrap profit, interest, hallmarking commission, testing fees.
     * Directly updates cash/bank account balances and handles GST Input Tax Credit (ITC) if applicable.
     */
    public function up(): void
    {
        Schema::create('expense_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique(); // e.g. EXP-2026-0001 or INC-2026-0001
            $table->date('entry_date')->index();
            $table->enum('entry_type', ['expense', 'income'])->index();

            // Account classification & Cash/Bank linkage
            $table->foreignId('account_id')->constrained('accounts'); // Expense head (e.g. Shop Rent) or Income head
            $table->foreignId('payment_account_id')->constrained('accounts'); // Cash Counter or Bank Account paid from/into

            $table->decimal('amount', 14, 2);
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'card', 'cheque'])->default('cash')->index();
            $table->string('reference_no', 100)->nullable(); // Bill no, receipt voucher no, UTR
            $table->text('description')->nullable();

            // GST ITC Claim (for registered entities claiming credit on business expenses)
            $table->boolean('is_gst_claimed')->default(false)->index();
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->string('vendor_gstin', 15)->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expense_incomes');
    }
};
