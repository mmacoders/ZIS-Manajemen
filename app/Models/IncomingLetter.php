<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncomingLetter extends Model
{
    use HasFactory;

    protected $table = 'incoming_letters';

    protected $fillable = [
        'nomor_agenda',
        'tanggal',
        'asal_surat',
        'perihal',
        'file_dokumen',
        'keterangan',
        'status',
        'program_id'
    ];

    protected $casts = [
        'tanggal' => 'date'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}