<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_aset',
        'kode_aset',
        'tahun_pengadaan',
        'kondisi',
        'lokasi',
        'nilai_aset',
        'keterangan',
        'rkat_id'
    ];

    protected $casts = [
        'tahun_pengadaan' => 'integer',
        'nilai_aset' => 'decimal:2'
    ];

    public function rkat()
    {
        return $this->belongsTo(Rkat::class);
    }
}