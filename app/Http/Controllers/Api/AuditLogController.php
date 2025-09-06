<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = AuditLog::with('user');

            // Filter by model type
            if ($request->model_type) {
                $query->where('model_type', $request->model_type);
            }

            // Filter by action
            if ($request->action) {
                $query->where('action', $request->action);
            }

            // Filter by user
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }

            // Date range filter
            if ($request->start_date) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Search by model ID
            if ($request->model_id) {
                $query->where('model_id', $request->model_id);
            }

            $perPage = $request->per_page ?? 20;
            $auditLogs = $query->orderBy('created_at', 'desc')
                             ->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $auditLogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil audit logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $auditLog = AuditLog::with('user')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $auditLog
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Audit log tidak ditemukan'
            ], 404);
        }
    }

    public function getModelActivity($modelType, $modelId)
    {
        try {
            $auditLogs = AuditLog::with('user')
                                ->where('model_type', $modelType)
                                ->where('model_id', $modelId)
                                ->orderBy('created_at', 'desc')
                                ->get();

            return response()->json([
                'success' => true,
                'data' => $auditLogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil activity log',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function summary(Request $request)
    {
        try {
            $startDate = $request->start_date ?? now()->startOfMonth();
            $endDate = $request->end_date ?? now()->endOfMonth();

            $summary = AuditLog::whereBetween('created_at', [$startDate, $endDate])
                              ->selectRaw('action, model_type, COUNT(*) as count')
                              ->groupBy('action', 'model_type')
                              ->get()
                              ->groupBy('model_type');

            $totalActivities = AuditLog::whereBetween('created_at', [$startDate, $endDate])->count();
            
            $topUsers = AuditLog::with('user')
                               ->whereBetween('created_at', [$startDate, $endDate])
                               ->whereNotNull('user_id')
                               ->selectRaw('user_id, COUNT(*) as activity_count')
                               ->groupBy('user_id')
                               ->orderBy('activity_count', 'desc')
                               ->limit(10)
                               ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'total_activities' => $totalActivities,
                    'summary_by_model' => $summary,
                    'top_users' => $topUsers
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil summary audit',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
