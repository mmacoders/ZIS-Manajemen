<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upz extends Model
{
    use HasFactory;

    protected $table = 'upz';

    protected $fillable = [
        'nama',
        'kode',
        'alamat',
        'pic_nama',
        'pic_telepon',
        'status',
        'tanggal_setoran',
        'jumlah_setoran',
        'bukti_transfer',
        'jenis_setoran',
        'validasi'
    ];

    protected $casts = [
        'tanggal_setoran' => 'date',
        'jumlah_setoran' => 'decimal:2'
    ];

    public function zisTransactions()
    {
        return $this->hasMany(ZisTransaction::class);
    }
}