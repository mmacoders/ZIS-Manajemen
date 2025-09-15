<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('spj', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_spj')->unique();
            $table->string('nama_penerima');
            $table->foreignId('program_id')->constrained('programs');
            $table->decimal('nominal', 15, 2);
            $table->date('tanggal_spj');
            $table->enum('status_validasi', ['sudah', 'belum'])->default('belum');
            $table->text('keterangan')->nullable();
            $table->string('dokumen_spj')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('spj');
    }
};