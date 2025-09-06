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
        'status'
    ];

    public function zisTransactions()
    {
        return $this->hasMany(ZisTransaction::class);
    }
}