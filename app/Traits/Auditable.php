<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logActivity($model, 'created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getAttributes();
            
            // Only log if there are actual changes
            if ($oldValues !== $newValues) {
                self::logActivity($model, 'updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            self::logActivity($model, 'deleted', $model->getAttributes(), null);
        });
    }

    protected static function logActivity($model, $action, $oldValues, $newValues)
    {
        $user = Auth::user();
        $request = request();
        
        AuditLog::create([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => $user ? $user->id : null,
            'ip_address' => $request ? $request->ip() : null,
            'user_agent' => $request ? $request->header('User-Agent') : null
        ]);
    }

    public function auditLogs()
    {
        return $this->morphMany(AuditLog::class, 'model', 'model_type', 'model_id');
    }
}