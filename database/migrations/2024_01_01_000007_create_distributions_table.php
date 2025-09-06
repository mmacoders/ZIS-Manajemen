<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_distribusi')->unique();
            $table->foreignId('program_id')->constrained('programs');
            $table->foreignId('mustahiq_id')->constrained('mustahiq');
            $table->decimal('jumlah', 15, 2);
            $table->date('tanggal_distribusi');
            $table->text('keterangan')->nullable();
            $table->string('bukti_distribusi')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->foreignId('distributed_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributions');
    }
};