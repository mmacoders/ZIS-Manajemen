<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutgoingLetter extends Model
{
    use HasFactory;

    protected $table = 'outgoing_letters';

    protected $fillable = [
        'nomor_surat',
        'tanggal',
        'tujuan',
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