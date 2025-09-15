<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Muzakki extends Model
{
    use HasFactory, Auditable;

    protected $table = 'muzakki';

    protected $fillable = [
        'nama',
        'nik',
        'alamat',
        'telepon',
        'email',
        'jenis',
        'keterangan',
        'gaji_pokok',
        'jenis_zakat',
        'nominal_setoran',
        'metode_pembayaran',
        'tanggal_setoran'
    ];

    protected $casts = [
        'gaji_pokok' => 'decimal:2',
        'nominal_setoran' => 'decimal:2',
        'tanggal_setoran' => 'date'
    ];

    public function zisTransactions()
    {
        return $this->hasMany(ZisTransaction::class);
    }
}