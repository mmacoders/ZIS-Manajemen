<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mustahiq extends Model
{
    use HasFactory;

    protected $table = 'mustahiq';

    protected $fillable = [
        'nama',
        'nik',
        'alamat',
        'telepon',
        'kategori',
        'keterangan',
        'status'
    ];

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }
}