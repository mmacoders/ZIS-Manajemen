<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FundDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_bukti',
        'program_id',
        'bidang_program',
        'anggaran_dialokasikan',
        'nominal_bantuan',
        'bukti_penyaluran',
        'tanggal_penyaluran',
        'status',
        'keterangan'
    ];

    protected $casts = [
        'tanggal_penyaluran' => 'date',
        'anggaran_dialokasikan' => 'decimal:2',
        'nominal_bantuan' => 'decimal:2'
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // Relationship with FundReceipt based on data-relationships.json
    public function fundReceipt()
    {
        return $this->belongsTo(FundReceipt::class);
    }

    // Relationship with Distribution based on data-relationships.json
    public function distribution()
    {
        return $this->belongsTo(Distribution::class, 'distribution_id');
    }

    // Relationship with SPJ based on data-relationships.json
    public function spj()
    {
        return $this->hasOne(Spj::class);
    }
}