<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->enum('jenis', ['masuk', 'keluar']);
            $table->string('asal_tujuan');
            $table->string('perihal');
            $table->date('tanggal_surat');
            $table->date('tanggal_diterima')->nullable();
            $table->text('isi_ringkas')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'processed', 'archived'])->default('pending');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};