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
        Schema::create('sharia_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 20)->unique(); // COA code
            $table->string('account_name', 200);
            $table->string('account_name_ar', 200)->nullable();
            $table->foreignId('fund_category_id')->constrained('sharia_fund_categories');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->enum('normal_balance', ['debit', 'credit']);
            $table->string('parent_code', 20)->nullable(); // For account hierarchy
            $table->integer('level')->default(1); // Account level (1-5)
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_baznas_required')->default(false); // Required for BAZNAS reporting
            $table->json('baznas_mapping')->nullable(); // BAZNAS report line mapping
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->index(['fund_category_id', 'is_active']);
            $table->index(['account_type', 'normal_balance']);
            $table->index('parent_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sharia_accounts');
    }
};
