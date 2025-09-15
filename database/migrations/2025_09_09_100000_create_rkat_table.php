<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rkat', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_urut');
            $table->string('bidang');
            $table->string('nama_program');
            $table->string('jenis_kegiatan');
            $table->integer('volume');
            $table->string('satuan');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('jumlah', 15, 2);
            $table->foreignId('program_id')->nullable()->constrained('programs');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rkat');
    }
};