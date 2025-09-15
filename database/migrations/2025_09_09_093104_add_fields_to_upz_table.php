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
        Schema::table('upz', function (Blueprint $table) {
            // Check if columns exist before adding them
            if (!Schema::hasColumn('upz', 'jumlah_setoran')) {
                $table->decimal('jumlah_setoran', 15, 2)->nullable()->after('tanggal_setoran');
            }
            if (!Schema::hasColumn('upz', 'bukti_transfer')) {
                $table->string('bukti_transfer')->nullable()->after('jumlah_setoran');
            }
            if (!Schema::hasColumn('upz', 'jenis_setoran')) {
                $table->enum('jenis_setoran', ['zakat', 'infaq', 'sedekah'])->nullable()->after('bukti_transfer');
            }
            if (!Schema::hasColumn('upz', 'validasi')) {
                $table->enum('validasi', ['pending', 'verified', 'rejected'])->default('pending')->after('jenis_setoran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('upz', function (Blueprint $table) {
            // Only drop columns that were actually added
            if (Schema::hasColumn('upz', 'jumlah_setoran')) {
                $table->dropColumn('jumlah_setoran');
            }
            if (Schema::hasColumn('upz', 'bukti_transfer')) {
                $table->dropColumn('bukti_transfer');
            }
            if (Schema::hasColumn('upz', 'jenis_setoran')) {
                $table->dropColumn('jenis_setoran');
            }
            if (Schema::hasColumn('upz', 'validasi')) {
                $table->dropColumn('validasi');
            }
        });
    }
};