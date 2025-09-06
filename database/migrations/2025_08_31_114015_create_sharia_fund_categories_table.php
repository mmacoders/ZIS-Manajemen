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
        Schema::create('sharia_fund_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique(); // ZAK, INF, SED, etc.
            $table->string('name', 100); // Zakat, Infaq, Sedekah
            $table->string('name_ar', 100)->nullable(); // Arabic name
            $table->text('description')->nullable();
            $table->enum('type', ['zakat', 'infaq', 'sedekah', 'amil', 'operational']);
            $table->decimal('amil_percentage', 5, 2)->default(12.5); // BAZNAS standard 12.5%
            $table->json('distribution_rules')->nullable(); // JSON rules for 8 asnaf
            $table->boolean('is_active')->default(true);
            $table->boolean('is_baznas_compliant')->default(true);
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sharia_fund_categories');
    }
};
