<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    public static function log($model, string $action, array $oldValues = [], array $newValues = []): void
    {
        AuditLog::create([
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => Auth::id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function logSystemActivity(string $action, string $description = ''): void
    {
        AuditLog::create([
            'model_type' => 'System',
            'model_id' => 0,
            'action' => $action,
            'old_values' => [],
            'new_values' => ['description' => $description],
            'user_id' => Auth::id() ?? null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    public static function getRecentActivities(int $limit = 50): array
    {
        return AuditLog::with(['user', 'user.role'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($log) {
                $modelName = class_basename($log->model_type);
                $action = $log->getFormattedAction();
                $userName = $log->user ? $log->user->name : 'System';
                $roleName = $log->user && $log->user->role ? $log->user->role->display_name ?? $log->user->role->name : 'Unknown';

                return [
                    'id' => $log->id,
                    'message' => "{$userName} ({$roleName}) {$action} {$modelName}",
                    'model_type' => $modelName,
                    'model_id' => $log->model_id,
                    'action' => $log->action,
                    'created_at' => $log->created_at->diffForHumans(),
                    'timestamp' => $log->created_at->toISOString(),
                    'user' => $log->user ? [
                        'name' => $log->user->name,
                        'role' => $log->user->role ? $log->user->role->name : null
                    ] : null
                ];
            })
            ->toArray();
    }
}