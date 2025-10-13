<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_bukti',
        'status_penerimaan',
        'tanggal_setor',
        'sumber_dana',
        'jumlah_setor',
        'jenis_dana',
        'donatur_id',
        'upz_id',
        'keterangan',
        'bukti_transfer'
    ];

    protected $casts = [
        'tanggal_setor' => 'date',
        'jumlah_setor' => 'decimal:2'
    ];

    public function donatur()
    {
        return $this->belongsTo(Donatur::class, 'donatur_id');
    }

    public function upz()
    {
        return $this->belongsTo(Upz::class);
    }

    // Relationship with ZisTransaction based on data-relationships.json
    public function zisTransaction()
    {
        return $this->belongsTo(ZisTransaction::class);
    }

    // Relationship with FundDistribution based on data-relationships.json
    public function fundDistribution()
    {
        return $this->hasOne(FundDistribution::class);
    }
}