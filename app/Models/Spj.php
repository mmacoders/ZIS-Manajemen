<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Spj extends Model
{
    use HasFactory;

    protected $table = 'spj';

    protected $fillable = [
        'nomor_spj',
        'nama_penerima',
        'program_id',
        'nominal',
        'tanggal_spj',
        'status_validasi',
        'keterangan',
        'dokumen_spj'
    ];

    protected $casts = [
        'tanggal_spj' => 'date',
        'nominal' => 'decimal:2'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // Relationship with FundDistribution based on data-relationships.json
    public function fundDistribution()
    {
        return $this->belongsTo(FundDistribution::class);
    }
}