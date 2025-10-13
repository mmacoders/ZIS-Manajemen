<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rkat;
use App\Models\FundReceipt;
use App\Models\FundDistribution;
use App\Models\Spj;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WakilBidang3Controller extends Controller
{
    public function dashboard()
    {
        $data = [
            'summary' => $this->getSummary(),
            'budget_analysis' => $this->getBudgetAnalysis(),
            'reports_status' => $this->getReportsStatus(),
            'recent_activities' => $this->getRecentActivities(),
        ];

        return response()->json($data);
    }

    private function getSummary()
    {
        $totalBudget = Rkat::sum('total_anggaran');
        $totalReceipts = FundReceipt::sum('jumlah');
        $totalDistributions = FundDistribution::sum('jumlah');
        
        return [
            'total_budget' => $totalBudget,
            'total_receipts' => $totalReceipts,
            'total_distributions' => $totalDistributions,
            'budget_utilization' => $totalBudget > 0 ? round(($totalDistributions / $totalBudget) * 100, 2) : 0,
        ];
    }

    private function getBudgetAnalysis()
    {
        $currentYear = Carbon::now()->year;
        
        // Get RKAT data for current year
        $rkatData = Rkat::whereYear('tahun', $currentYear)
            ->selectRaw('SUM(total_anggaran) as total_budget, SUM(realisasi) as total_realization')
            ->first();
            
        // Monthly budget absorption
        $monthlyAbsorption = Rkat::whereYear('tahun', $currentYear)
            ->selectRaw('MONTH(tahun) as month, SUM(total_anggaran) as budget, SUM(realisasi) as realization')
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'month' => $item->month,
                    'budget' => $item->budget,
                    'realization' => $item->realization,
                    'absorption_rate' => $item->budget > 0 ? round(($item->realization / $item->budget) * 100, 2) : 0,
                ];
            });

        return [
            'annual_summary' => [
                'total_budget' => $rkatData->total_budget ?? 0,
                'total_realization' => $rkatData->total_realization ?? 0,
                'absorption_rate' => ($rkatData->total_budget ?? 0) > 0 ? 
                    round(($rkatData->total_realization / $rkatData->total_budget) * 100, 2) : 0,
            ],
            'monthly_absorption' => $monthlyAbsorption,
        ];
    }

    private function getReportsStatus()
    {
        // Get SPJ status
        $spjByStatus = Spj::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->keyBy('status');
            
        return [
            'total_spj' => Spj::count(),
            'pending_spj' => $spjByStatus->get('pending')->count ?? 0,
            'approved_spj' => $spjByStatus->get('approved')->count ?? 0,
            'rejected_spj' => $spjByStatus->get('rejected')->count ?? 0,
            'submitted_spj' => $spjByStatus->get('submitted')->count ?? 0,
        ];
    }

    private function getRecentActivities()
    {
        // Combine different activities
        $activities = [];
        
        // Recent RKAT activities
        $rkatActivities = Rkat::latest()->take(3)->get()->map(function($rkat) {
            return [
                'id' => $rkat->id,
                'type' => 'rkat',
                'description' => "RKAT baru untuk {$rkat->tahun} - {$rkat->keterangan}",
                'amount' => $rkat->total_anggaran,
                'date' => $rkat->created_at,
            ];
        });
        
        // Recent Fund Receipt activities
        $receiptActivities = FundReceipt::with('rkat')->latest()->take(3)->get()->map(function($receipt) {
            return [
                'id' => $receipt->id,
                'type' => 'fund_receipt',
                'description' => "Penerimaan dana: {$receipt->keterangan}",
                'amount' => $receipt->jumlah,
                'date' => $receipt->tanggal_penerimaan,
            ];
        });
        
        // Recent SPJ activities
        $spjActivities = Spj::with('rkat')->latest()->take(4)->get()->map(function($spj) {
            return [
                'id' => $spj->id,
                'type' => 'spj',
                'description' => "SPJ {$spj->status}: {$spj->keterangan}",
                'amount' => $spj->total_pengeluaran,
                'date' => $spj->tanggal_pelaporan,
            ];
        });
        
        $activities = collect(array_merge(
            $rkatActivities->toArray(),
            $receiptActivities->toArray(),
            $spjActivities->toArray()
        ))->sortByDesc('date')->take(10)->values();
        
        return $activities;
    }
}