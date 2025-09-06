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
        'jumlah',
        'tanggal_distribusi',
        'keterangan',
        'bukti_distribusi',
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
}