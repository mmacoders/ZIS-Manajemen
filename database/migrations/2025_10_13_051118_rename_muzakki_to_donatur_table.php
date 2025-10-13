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
        // Rename the muzakki table to donatur
        Schema::rename('muzakki', 'donatur');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename the donatur table back to muzakki
        Schema::rename('donatur', 'muzakki');
    }
};