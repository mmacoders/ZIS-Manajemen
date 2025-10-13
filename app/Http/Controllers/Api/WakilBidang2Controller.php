<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Distribution;
use App\Models\Mustahiq;
use App\Models\Program;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WakilBidang2Controller extends Controller
{
    public function dashboard()
    {
        $data = [
            'summary' => $this->getSummary(),
            'charts' => $this->getChartData(),
            'program_outcomes' => $this->getProgramOutcomes(),
            'recent_activities' => $this->getRecentActivities(),
        ];

        return response()->json($data);
    }

    private function getSummary()
    {
        return [
            'total_mustahiq' => Mustahiq::where('status', 'aktif')->count(),
            'total_programs' => Program::where('status', 'aktif')->count(),
            'total_distributed' => Distribution::where('status', 'completed')->sum('jumlah'),
            'pending_distributions' => Distribution::where('status', 'pending')->count(),
        ];
    }

    private function getChartData()
    {
        $startDate = Carbon::now()->subMonths(12);
        $endDate = Carbon::now();

        // Monthly distribution data
        $monthlyDistribution = Distribution::where('status', 'completed')
            ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
            ->selectRaw('YEAR(tanggal_distribusi) as year, MONTH(tanggal_distribusi) as month, SUM(jumlah) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Distribution by program
        $distributionByProgram = Distribution::where('status', 'completed')
            ->with('program')
            ->selectRaw('program_id, SUM(jumlah) as total, COUNT(*) as count')
            ->groupBy('program_id')
            ->get()
            ->map(function($item) {
                return [
                    'program_name' => $item->program->nama ?? 'Unknown Program',
                    'total' => $item->total,
                    'count' => $item->count,
                ];
            });

        // Mustahiq by category
        $mustahiqByCategory = Mustahiq::where('status', 'aktif')
            ->selectRaw('kategori, COUNT(*) as count')
            ->groupBy('kategori')
            ->get();

        return [
            'monthly_distribution' => $monthlyDistribution,
            'distribution_by_program' => $distributionByProgram,
            'mustahiq_by_category' => $mustahiqByCategory,
        ];
    }

    private function getProgramOutcomes()
    {
        // Get programs with their distribution data
        $programs = Program::where('status', 'aktif')
            ->withCount('distributions')
            ->withSum('distributions as total_distributed', 'jumlah')
            ->take(10)
            ->get()
            ->map(function($program) {
                return [
                    'id' => $program->id,
                    'name' => $program->nama,
                    'type' => $program->jenis,
                    'distributions_count' => $program->distributions_count,
                    'total_distributed' => $program->total_distributed ?? 0,
                    'target_amount' => $program->target_amount ?? 0,
                    'completion_percentage' => $program->target_amount > 0 ? 
                        round(($program->total_distributed / $program->target_amount) * 100, 2) : 0,
                ];
            });

        return $programs;
    }

    private function getRecentActivities()
    {
        return Distribution::with(['program', 'mustahiq'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function($distribution) {
                return [
                    'id' => $distribution->id,
                    'type' => 'distribution',
                    'description' => "Distribusi ke {$distribution->mustahiq->nama} untuk program {$distribution->program->nama}",
                    'amount' => $distribution->jumlah,
                    'date' => $distribution->tanggal_distribusi,
                    'status' => $distribution->status,
                ];
            });
    }
}