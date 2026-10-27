<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Chart of Accounts (Account Groups & Accounts):
     * Foundation for financial accounting:
     * - Cash in Hand, Bank Accounts (maintaining cash balance)
     * - Income Accounts (Sales, Making Charges Income, Scrap Sale)
     * - Expenditure Accounts (Rent, Electricity, Karigar wages, Salary, Tea & Refreshment)
     * - Tax Ledgers (CGST Output, SGST Output, IGST Output, CGST Input, SGST Input, IGST Input)
     * - Customer (Sundry Debtors) and Vendor (Sundry Creditors) accounts.
     */
    public function up(): void
    {
        Schema::create('account_groups', function (Blueprint $table) {
            $table->id();
            $table->string('group_name', 100);
            $table->string('group_code', 30)->unique(); // e.g. ASST_CASH, ASST_BANK, LIAB_DUTIES_TAXES, INC_DIRECT, EXP_INDIRECT
            $table->enum('primary_type', ['asset', 'liability', 'equity', 'income', 'expense'])->index();
            $table->foreignId('parent_id')->nullable()->constrained('account_groups')->nullOnDelete();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name', 150);
            $table->string('account_code', 40)->unique(); // e.g. CASH_COUNTER, SBI_CURRENT, TAX_CGST_OUTPUT, EXP_RENT
            $table->foreignId('account_group_id')->constrained('account_groups');
            $table->enum('account_type', [
                'cash',
                'bank',
                'customer',
                'vendor',
                'tax_payable',
                'tax_receivable',
                'direct_income',
                'indirect_income',
                'direct_expense',
                'indirect_expense',
                'general'
            ])->index();

            // Banking details (for bank account heads)
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->string('branch', 100)->nullable();

            // Balances
            $table->decimal('opening_balance', 14, 2)->default(0.00);
            $table->enum('opening_balance_type', ['debit', 'credit'])->default('debit');
            $table->decimal('current_balance', 14, 2)->default(0.00)->index();

            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_system')->default(false); // Protect core system accounts from deletion
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('account_groups');
    }
};
