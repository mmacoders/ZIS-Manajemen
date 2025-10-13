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
        // Rename muzakki_id columns to donatur_id in relevant tables
        Schema::table('zis_transactions', function (Blueprint $table) {
            $table->renameColumn('muzakki_id', 'donatur_id');
        });

        Schema::table('sharia_transactions', function (Blueprint $table) {
            $table->renameColumn('muzakki_id', 'donatur_id');
        });

        Schema::table('fund_receipts', function (Blueprint $table) {
            $table->renameColumn('muzakki_id', 'donatur_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rename donatur_id columns back to muzakki_id in relevant tables
        Schema::table('zis_transactions', function (Blueprint $table) {
            $table->renameColumn('donatur_id', 'muzakki_id');
        });

        Schema::table('sharia_transactions', function (Blueprint $table) {
            $table->renameColumn('donatur_id', 'muzakki_id');
        });

        Schema::table('fund_receipts', function (Blueprint $table) {
            $table->renameColumn('donatur_id', 'muzakki_id');
        });
    }
};