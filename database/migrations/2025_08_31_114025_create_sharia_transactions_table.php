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
        Schema::create('sharia_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number', 50)->unique();
            $table->date('transaction_date');
            $table->enum('transaction_type', ['collection', 'distribution', 'amil_allocation', 'operational', 'adjustment']);
            $table->foreignId('fund_category_id')->constrained('sharia_fund_categories');
            $table->foreignId('debit_account_id')->constrained('sharia_accounts');
            $table->foreignId('credit_account_id')->constrained('sharia_accounts');
            $table->decimal('amount', 15, 2);
            $table->decimal('amil_amount', 15, 2)->default(0); // Automatically calculated amil portion
            $table->string('reference_type')->nullable(); // zis_transactions, distributions, etc.
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the referenced record
            $table->foreignId('muzakki_id')->nullable()->constrained('muzakki');
            $table->foreignId('mustahiq_id')->nullable()->constrained('mustahiq');
            $table->string('mustahiq_category', 50)->nullable(); // 8 asnaf category
            $table->text('description');
            $table->text('baznas_notes')->nullable(); // Special notes for BAZNAS compliance
            $table->boolean('is_baznas_compliant')->default(true);
            $table->json('compliance_data')->nullable(); // Additional compliance metadata
            $table->enum('status', ['pending', 'approved', 'rejected', 'posted'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            
            $table->index(['transaction_date', 'transaction_type']);
            $table->index(['fund_category_id', 'status']);
            $table->index(['reference_type', 'reference_id']);
            $table->index(['muzakki_id', 'mustahiq_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sharia_transactions');
    }
};
