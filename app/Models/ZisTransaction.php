<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class ZisTransaction extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'nomor_transaksi',
        'muzakki_id',
        'upz_id',
        'jenis_zis',
        'jumlah',
        'tanggal_transaksi',
        'keterangan',
        'bukti_transfer',
        'status',
        'verified_by',
        'verified_at'
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'verified_at' => 'datetime',
        'jumlah' => 'decimal:2'
    ];

    public function muzakki()
    {
        return $this->belongsTo(Muzakki::class);
    }

    public function upz()
    {
        return $this->belongsTo(Upz::class);
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Relationship with FundReceipt based on data-relationships.json
    public function fundReceipt()
    {
        return $this->hasOne(FundReceipt::class);
    }
}