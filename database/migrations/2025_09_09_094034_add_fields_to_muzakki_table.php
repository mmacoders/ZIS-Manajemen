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
            $table->decimal('gaji_pokok', 15, 2)->nullable()->after('jenis');
            $table->enum('jenis_zakat', ['zakat penghasilan', 'zakat mal', 'zakat fitrah', 'infaq', 'sedekah'])->nullable()->after('gaji_pokok');
            $table->decimal('nominal_setoran', 15, 2)->nullable()->after('jenis_zakat');
            $table->enum('metode_pembayaran', ['tunai', 'transfer bank', 'e-wallet', 'lainnya'])->nullable()->after('nominal_setoran');
            $table->date('tanggal_setoran')->nullable()->after('metode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('muzakki', function (Blueprint $table) {
            $table->dropColumn(['gaji_pokok', 'jenis_zakat', 'nominal_setoran', 'metode_pembayaran', 'tanggal_setoran']);
        });
    }
};