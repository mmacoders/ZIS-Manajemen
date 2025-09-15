<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rkat extends Model
{
    use HasFactory;

    protected $table = 'rkat';

    protected $fillable = [
        'nomor_urut',
        'bidang',
        'nama_program',
        'jenis_kegiatan',
        'volume',
        'satuan',
        'harga_satuan',
        'jumlah',
        'program_id'
    ];

    protected $casts = [
        'harga_satuan' => 'decimal:2',
        'jumlah' => 'decimal:2'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // Relationship with Asset based on data-relationships.json
    public function assets()
    {
        return $this->hasMany(Asset::class, 'rkat_id');
    }
}