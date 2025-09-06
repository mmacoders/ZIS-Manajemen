<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_surat',
        'jenis',
        'asal_tujuan',
        'perihal',
        'tanggal_surat',
        'tanggal_diterima',
        'isi_ringkas',
        'file_path',
        'status',
        'created_by'
    ];

    protected $casts = [
        'tanggal_surat' => 'date',
        'tanggal_diterima' => 'date'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}