<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Financial Vouchers & Voucher Entries (Double Entry General Ledger):
     * Guarantees audit compliance, ledger posting, and balance sheet integrity.
     * Supports receipt, payment, contra (cash-to-bank), sales journal, purchase journal,
     * and expense vouchers.
     */
    public function up(): void
    {
        Schema::create('financial_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique(); // e.g. VCH-2026-0001
            $table->date('voucher_date')->index();
            $table->enum('voucher_type', [
                'receipt',
                'payment',
                'contra',
                'sales_journal',
                'purchase_journal',
                'expense',
                'income',
                'journal'
            ])->index();
            $table->string('reference_type', 50)->nullable()->index(); // e.g. 'sale', 'purchase', 'expense_income', 'direct'
            $table->unsignedBigInteger('reference_id')->nullable()->index();
            $table->decimal('total_amount', 14, 2);
            $table->text('narration')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('financial_voucher_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('financial_voucher_id')->constrained('financial_vouchers')->cascadeOnDelete();
            $table->foreignId('account_id')->constrained('accounts');
            $table->enum('entry_type', ['debit', 'credit'])->index();
            $table->decimal('amount', 14, 2);
            $table->string('narration', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_voucher_entries');
        Schema::dropIfExists('financial_vouchers');
    }
};
