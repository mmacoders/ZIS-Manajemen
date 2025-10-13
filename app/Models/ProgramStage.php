<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id',
        'stage_name',
        'description',
        'order',
        'status',
        'approved_by',
        'approved_at',
        'is_locked',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isApproved()
    {
        return !is_null($this->approved_by) && !is_null($this->approved_at);
    }

    public function isLocked()
    {
        return $this->is_locked;
    }
}