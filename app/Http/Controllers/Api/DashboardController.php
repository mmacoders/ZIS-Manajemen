<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZisTransaction;
use App\Models\Distribution;
use App\Models\Document;
use App\Models\Donatur;
use App\Models\Mustahiq;
use App\Models\Program;
use App\Models\Upz;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'summary' => $this->getSummary(),
            'recent_transactions' => $this->getRecentTransactions(),
            'recent_distributions' => $this->getRecentDistributions(),
            'charts' => $this->getChartData(),
            'notifications' => $this->getAdminNotifications(),
        ];

        return response()->json($data);
    }

    private function getSummary()
    {
        return [
            'total_donatur' => Donatur::count(),
            'total_upz' => Upz::where('status', 'aktif')->count(),
            'total_mustahiq' => Mustahiq::where('status', 'aktif')->count(),
            'total_programs' => Program::where('status', 'aktif')->count(),
            'total_zis_collected' => ZisTransaction::where('status', 'verified')->sum('jumlah'),
            'total_distributed' => Distribution::where('status', 'completed')->sum('jumlah'),
            'total_documents' => Document::count(),
            'pending_transactions' => ZisTransaction::where('status', 'pending')->count(),
            'pending_documents' => Document::where('status', 'pending')->count(),
        ];
    }

    private function getRecentTransactions()
    {
        return ZisTransaction::with(['donatur', 'upz'])
            ->latest()
            ->take(5)
            ->get();
    }

    private function getRecentDistributions()
    {
        return Distribution::with(['program', 'mustahiq'])
            ->latest()
            ->take(5)
            ->get();
    }

    private function getChartData()
    {
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();

        // Monthly ZIS collection data
        $monthlyCollection = ZisTransaction::where('status', 'verified')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->selectRaw('YEAR(tanggal_transaksi) as year, MONTH(tanggal_transaksi) as month, SUM(jumlah) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Monthly distribution data
        $monthlyDistribution = Distribution::where('status', 'completed')
            ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
            ->selectRaw('YEAR(tanggal_distribusi) as year, MONTH(tanggal_distribusi) as month, SUM(jumlah) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // ZIS by type
        $zisByType = ZisTransaction::where('status', 'verified')
            ->selectRaw('jenis_zis, SUM(jumlah) as total, COUNT(*) as count')
            ->groupBy('jenis_zis')
            ->get();

        // Mustahiq by category
        $mustahiqByCategory = Mustahiq::where('status', 'aktif')
            ->selectRaw('kategori, COUNT(*) as count')
            ->groupBy('kategori')
            ->get();

        // Top Donatur (last 12 months)
        $topDonatur = ZisTransaction::where('status', 'verified')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->with('donatur')
            ->selectRaw('
                donatur_id,
                SUM(jumlah) as total_contribution,
                COUNT(*) as total_transactions
            ')
            ->groupBy('donatur_id')
            ->orderBy('total_contribution', 'desc')
            ->limit(10)
            ->get();

        return [
            'monthly_collection' => $monthlyCollection,
            'monthly_distribution' => $monthlyDistribution,
            'zis_by_type' => $zisByType,
            'mustahiq_by_category' => $mustahiqByCategory,
            'top_donatur' => $topDonatur,
        ];
    }

    private function getAdminNotifications()
    {
        // Get recent activities from bidang 1 (collection) and bidang 2 (distribution)
        $recentActivities = AuditLog::with(['user', 'user.role'])
            ->whereHas('user.role', function($query) {
                $query->whereIn('name', ['bidang1', 'bidang2']);
            })
            ->where('created_at', '>=', Carbon::now()->subHours(24)) // Last 24 hours
            ->orderBy('created_at', 'desc')
            ->take(10)
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
            });

        // Get pending items that need admin attention
        $pendingTransactions = ZisTransaction::where('status', 'pending')
            ->count();

        $pendingDocuments = Document::where('status', 'pending')
            ->count();

        return [
            'recent_activities' => $recentActivities,
            'pending_items' => [
                'transactions' => $pendingTransactions,
                'documents' => $pendingDocuments,
                'total' => $pendingTransactions + $pendingDocuments
            ],
            'unread_count' => $recentActivities->count() + ($pendingTransactions + $pendingDocuments)
        ];
    }
}