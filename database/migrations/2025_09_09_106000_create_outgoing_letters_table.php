<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('outgoing_letters', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat')->unique();
            $table->date('tanggal');
            $table->string('tujuan');
            $table->string('perihal');
            $table->string('file_dokumen')->nullable();
            $table->text('keterangan')->nullable();
            $table->enum('status', ['draft', 'dikirim', 'diterima'])->default('draft');
            $table->foreignId('program_id')->nullable()->constrained('programs');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('outgoing_letters');
    }
};