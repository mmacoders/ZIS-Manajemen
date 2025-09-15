<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_distribusi',
        'program_id',
        'mustahiq_id',
        'jenis_bantuan',
        'jumlah',
        'tanggal_distribusi',
        'bukti_distribusi',
        'keterangan',
        'status',
        'distributed_by'
    ];

    protected $casts = [
        'tanggal_distribusi' => 'date',
        'jumlah' => 'decimal:2'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function mustahiq()
    {
        return $this->belongsTo(Mustahiq::class);
    }

    public function distributor()
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }

    // Relationship with FundDistribution based on data-relationships.json
    public function fundDistribution()
    {
        return $this->hasOne(FundDistribution::class);
    }
}