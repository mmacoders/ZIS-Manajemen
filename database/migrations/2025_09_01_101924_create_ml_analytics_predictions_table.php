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
        Schema::create('ml_analytics_predictions', function (Blueprint $table) {
            $table->id();
            $table->string('prediction_type'); // 'donation', 'beneficiary_need', 'fraud_risk'
            $table->string('model_type'); // 'linear_regression', 'pattern_analysis', 'anomaly_detection'
            $table->unsignedBigInteger('target_id')->nullable(); // ID of muzakki, mustahiq, etc.
            $table->string('target_type')->nullable(); // Model class name
            $table->json('input_data'); // Input features used for prediction
            $table->json('prediction_result'); // Prediction output
            $table->decimal('confidence_score', 5, 2); // Confidence percentage
            $table->decimal('accuracy_score', 5, 2)->nullable(); // Actual accuracy if verified
            $table->date('prediction_date'); // Date prediction is for
            $table->json('model_parameters')->nullable(); // Model parameters used
            $table->boolean('is_verified')->default(false); // Whether prediction was verified
            $table->decimal('actual_value', 15, 2)->nullable(); // Actual outcome for learning
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->index(['prediction_type', 'prediction_date']);
            $table->index(['target_type', 'target_id']);
            $table->index('confidence_score');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_analytics_predictions');
    }
};
