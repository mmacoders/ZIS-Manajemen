<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('fund_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_bukti')->unique();
            $table->enum('status_penerimaan', ['diterima', 'ditolak', 'pending'])->default('pending');
            $table->date('tanggal_setor');
            $table->string('sumber_dana');
            $table->decimal('jumlah_setor', 15, 2);
            $table->string('jenis_dana');
            $table->foreignId('muzakki_id')->nullable()->constrained('muzakki');
            $table->foreignId('upz_id')->nullable()->constrained('upz');
            $table->text('keterangan')->nullable();
            $table->string('bukti_transfer')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('fund_receipts');
    }
};