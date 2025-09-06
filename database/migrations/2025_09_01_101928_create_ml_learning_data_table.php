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
        Schema::create('ml_learning_data', function (Blueprint $table) {
            $table->id();
            $table->string('data_type'); // 'donor_pattern', 'distribution_pattern', 'fraud_indicator'
            $table->string('entity_type'); // 'muzakki', 'mustahiq', 'transaction', 'distribution'
            $table->unsignedBigInteger('entity_id');
            $table->json('features'); // Feature vector for ML
            $table->json('labels'); // Target values/classifications
            $table->decimal('weight', 5, 4)->default(1.0000); // Data point weight for learning
            $table->boolean('is_training_data')->default(true);
            $table->boolean('is_validated')->default(false);
            $table->json('metadata')->nullable(); // Additional context data
            $table->timestamp('data_period_start')->nullable(); // Time period this data represents
            $table->timestamp('data_period_end')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index(['data_type', 'entity_type']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('is_training_data');
            $table->index(['data_period_start', 'data_period_end']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_learning_data');
    }
};
