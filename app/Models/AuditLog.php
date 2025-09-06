<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'user_id',
        'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime'
    ];

    public $timestamps = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getModelClass()
    {
        return $this->model_type;
    }

    public function getFormattedAction(): string
    {
        return match($this->action) {
            'created' => 'Dibuat',
            'updated' => 'Diperbarui', 
            'deleted' => 'Dihapus',
            default => ucfirst($this->action)
        };
    }
}
