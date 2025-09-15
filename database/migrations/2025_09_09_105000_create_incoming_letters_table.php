<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('incoming_letters', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->unique();
            $table->date('tanggal');
            $table->string('asal_surat');
            $table->string('perihal');
            $table->string('file_dokumen')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai'])->default('baru');
            $table->foreignId('program_id')->nullable()->constrained('programs');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('incoming_letters');
    }
};