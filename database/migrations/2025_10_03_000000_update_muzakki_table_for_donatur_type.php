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
        Schema::table('muzakki', function (Blueprint $table) {
            // Change the 'jenis' column to include the new donor type
            $table->dropColumn('jenis');
            $table->enum('jenis_donatur', ['individu', 'lembaga', 'munfiq'])->after('email');
            
            // Add gender column for individual donors
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan'])->nullable()->after('jenis_donatur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropColumn(['jenis_donatur', 'jenis_kelamin']);
            $table->enum('jenis', ['individu', 'perusahaan'])->after('email');
        });
    }
};