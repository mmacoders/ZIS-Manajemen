<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jenis_program',
        'bidang_program',
        'deskripsi',
        'target_dana',
        'dana_terkumpul',
        'tanggal_mulai',
        'tanggal_selesai',
        'penanggung_jawab',
        'status',
        'created_by'
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'target_dana' => 'decimal:2',
        'dana_terkumpul' => 'decimal:2'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function distributions()
    {
        return $this->hasMany(Distribution::class);
    }
    
    // Accessor to display bidang program label
    public function getBidangProgramLabelAttribute()
    {
        $labels = [
            'kemanusiaan' => 'Kemanusiaan',
            'pendidikan_distribusi' => 'Pendidikan',
            'dakwah_dan_advokasi' => 'Dakwah dan Advokasi',
            'kesehatan_distribusi' => 'Kesehatan',
            'ekonomi_produk' => 'Ekonomi Produk',
            'pendidikan_pemberdayaan' => 'Pendidikan',
            'kesehatan_pemberdayaan' => 'Kesehatan'
        ];
        
        return $labels[$this->bidang_program] ?? $this->bidang_program;
    }
    
    // Accessor to display jenis program label
    public function getJenisProgramLabelAttribute()
    {
        $labels = [
            'distribusi' => 'Distribusi',
            'pemberdayaan' => 'Pemberdayaan'
        ];
        
        return $labels[$this->jenis_program] ?? $this->jenis_program;
    }

    // Relationship with RKAT based on data-relationships.json
    public function rkat()
    {
        return $this->hasMany(Rkat::class);
    }

    // Relationship with Staff based on data-relationships.json
    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    // Relationship with IncomingLetter based on data-relationships.json
    public function incomingLetters()
    {
        return $this->hasMany(IncomingLetter::class);
    }

    // Relationship with OutgoingLetter based on data-relationships.json
    public function outgoingLetters()
    {
        return $this->hasMany(OutgoingLetter::class);
    }

    // Relationship with FundDistribution based on data-relationships.json
    public function fundDistributions()
    {
        return $this->hasMany(FundDistribution::class);
    }

    // Relationship with SPJ based on data-relationships.json
    public function spj()
    {
        return $this->hasMany(Spj::class);
    }
}