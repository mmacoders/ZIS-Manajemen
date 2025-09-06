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
        Schema::create('ml_analytics_cache', function (Blueprint $table) {
            $table->id();
            $table->string('cache_key')->unique(); // Unique identifier for cached result
            $table->string('analytics_type'); // 'donor_patterns', 'predictions', 'fraud_detection'
            $table->json('cache_data'); // Cached analytics result
            $table->json('cache_metadata')->nullable(); // Metadata about the cache
            $table->timestamp('computed_at'); // When the data was computed
            $table->timestamp('expires_at'); // When the cache expires
            $table->boolean('is_valid')->default(true); // Whether cache is still valid
            $table->string('data_version')->nullable(); // Version of underlying data
            $table->integer('hit_count')->default(0); // Number of times accessed
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamps();
            
            $table->index('cache_key');
            $table->index(['analytics_type', 'expires_at']);
            $table->index('is_valid');
            $table->index('computed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ml_analytics_cache');
    }
};
