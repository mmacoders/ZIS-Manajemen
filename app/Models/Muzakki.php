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
        'keterangan'
    ];

    public function zisTransactions()
    {
        return $this->hasMany(ZisTransaction::class);
    }
}