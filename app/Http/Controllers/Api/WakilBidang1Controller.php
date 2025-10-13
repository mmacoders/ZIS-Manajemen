<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZisTransaction;
use App\Models\Donatur;
use App\Models\Upz;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WakilBidang1Controller extends Controller
{
    public function dashboard()
    {
        $data = [
            'summary' => $this->getSummary(),
            'charts' => $this->getChartData(),
            'targets' => $this->getTargetsAndRealizations(),
            'recent_activities' => $this->getRecentActivities(),
        ];

        return response()->json($data);
    }

    private function getSummary()
    {
        return [
            'total_donatur' => Donatur::count(),
            'total_upz' => Upz::where('status', 'aktif')->count(),
            'total_zis_collected' => ZisTransaction::where('status', 'verified')->sum('jumlah'),
            'pending_transactions' => ZisTransaction::where('status', 'pending')->count(),
        ];
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

        // ZIS by type
        $zisByType = ZisTransaction::where('status', 'verified')
            ->selectRaw('jenis_zis, SUM(jumlah) as total, COUNT(*) as count')
            ->groupBy('jenis_zis')
            ->get();

        return [
            'monthly_collection' => $monthlyCollection,
            'zis_by_type' => $zisByType,
        ];
    }

    private function getTargetsAndRealizations()
    {
        // This would typically come from a targets table in a real implementation
        // For now, we'll simulate with sample data
        $currentYear = Carbon::now()->year;
        
        return [
            'collection_target' => 5000000000, // 5 billion IDR target
            'collection_realization' => ZisTransaction::where('status', 'verified')
                ->whereYear('tanggal_transaksi', $currentYear)
                ->sum('jumlah'),
            'donatur_target' => 10000,
            'donatur_realization' => Donatur::count(),
        ];
    }

    private function getRecentActivities()
    {
        return ZisTransaction::with(['donatur', 'upz'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($transaction) {
                return [
                    'id' => $transaction->id,
                    'type' => 'transaksi_zis',
                    'description' => "Transaksi ZIS baru dari {$transaction->donatur->nama}",
                    'amount' => $transaction->jumlah,
                    'date' => $transaction->tanggal_transaksi,
                    'status' => $transaction->status,
                ];
            });
    }
}