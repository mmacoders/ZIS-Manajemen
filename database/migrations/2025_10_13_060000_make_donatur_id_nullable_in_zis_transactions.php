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
        // Make donatur_id nullable to support operational transactions
        Schema::table('zis_transactions', function (Blueprint $table) {
            $table->foreignId('donatur_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Make donatur_id non-nullable again
        Schema::table('zis_transactions', function (Blueprint $table) {
            $table->foreignId('donatur_id')->nullable(false)->change();
        });
    }
};