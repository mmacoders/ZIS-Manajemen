<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aset');
            $table->string('kode_aset')->unique();
            $table->integer('tahun_pengadaan');
            $table->enum('kondisi', ['baik', 'rusak', 'perlu_perbaikan']);
            $table->string('lokasi');
            $table->decimal('nilai_aset', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->foreignId('rkat_id')->nullable()->constrained('rkat');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
};