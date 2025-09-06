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
        Schema::create('baznas_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_code', 20)->unique(); // Monthly, quarterly, annual codes
            $table->enum('report_type', ['monthly', 'quarterly', 'annual', 'special']);
            $table->enum('report_period', ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december', 'q1', 'q2', 'q3', 'q4', 'annual']);
            $table->year('report_year');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('total_collection', 15, 2)->default(0);
            $table->decimal('total_zakat', 15, 2)->default(0);
            $table->decimal('total_infaq', 15, 2)->default(0);
            $table->decimal('total_sedekah', 15, 2)->default(0);
            $table->decimal('total_amil', 15, 2)->default(0);
            $table->decimal('total_distribution', 15, 2)->default(0);
            $table->json('asnaf_distribution')->nullable(); // Distribution per 8 asnaf
            $table->json('geographical_distribution')->nullable(); // Per province/city
            $table->json('program_distribution')->nullable(); // Per program type
            $table->json('collection_sources')->nullable(); // Individual, corporate, etc.
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->json('compliance_metrics')->nullable(); // BAZNAS KPIs
            $table->text('executive_summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->enum('status', ['draft', 'review', 'approved', 'submitted'])->default('draft');
            $table->string('file_path')->nullable(); // Generated PDF path
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('prepared_by')->constrained('users');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['report_type', 'report_year', 'report_period']);
            $table->index(['status', 'submitted_at']);
            $table->unique(['report_type', 'report_year', 'report_period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baznas_reports');
    }
};
