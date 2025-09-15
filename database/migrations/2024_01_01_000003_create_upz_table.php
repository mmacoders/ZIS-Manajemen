<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('upz', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->unique();
            $table->text('alamat');
            $table->string('pic_nama');
            $table->string('pic_telepon');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            // Additional fields for form completeness requirements
            $table->date('tanggal_setoran')->nullable();
            $table->decimal('jumlah_setoran', 15, 2)->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->enum('jenis_setoran', ['zakat', 'infaq', 'sedekah'])->nullable();
            $table->enum('validasi', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('upz');
    }
};