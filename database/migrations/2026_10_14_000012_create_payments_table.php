<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Payments (Cash & Bank Book Transactions):
     * Maintains running cash balances and bank transaction history.
     * Logs both receipts (inward) and disbursements (outward) with payment mode
     * (Cash, UPI, NEFT/RTGS, Card, Cheque).
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number', 50)->unique(); // e.g. PAY-2026-0001
            $table->date('payment_date')->index();
            $table->enum('payment_type', ['receipt', 'payment'])->index(); // 'receipt' = money in, 'payment' = money out
            $table->foreignId('account_id')->constrained('accounts'); // Cash account or Bank account touched
            $table->foreignId('party_id')->nullable()->constrained('parties')->nullOnDelete();

            // Source linkage
            $table->enum('reference_type', ['sale', 'purchase', 'expense', 'income', 'advance', 'other'])->default('other')->index();
            $table->unsignedBigInteger('reference_id')->nullable()->index(); // sale_id or purchase_id

            // Instrument details
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'card', 'cheque'])->default('cash')->index();
            $table->string('transaction_reference', 100)->nullable(); // UTR, UPI transaction ID, Cheque No, Card Auth
            $table->decimal('amount', 14, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
