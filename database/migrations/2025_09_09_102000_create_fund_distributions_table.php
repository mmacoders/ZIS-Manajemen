<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fund_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bukti')->unique();
            $table->foreignId('program_id')->constrained('programs');
            $table->string('bidang_program');
            $table->decimal('anggaran_dialokasikan', 15, 2);
            $table->decimal('nominal_bantuan', 15, 2);
            $table->string('bukti_penyaluran')->nullable();
            $table->date('tanggal_penyaluran');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fund_distributions');
    }
};