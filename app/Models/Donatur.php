<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Donatur extends Model
{
    use HasFactory, Auditable;

    protected $table = 'donatur';

    protected $fillable = [
        'nama',
        'nik',
        'npwp',
        'alamat',
        'telepon',
        'email',
        'jenis_donatur',
        'jenis_kelamin',
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