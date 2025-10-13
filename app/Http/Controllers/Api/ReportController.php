<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ZisTransaction;
use App\Models\Distribution;
use App\Models\Donatur;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function summary(Request $request)
    {
        try {
            $startDate = $request->start_date ?? now()->startOfMonth();
            $endDate = $request->end_date ?? now()->endOfMonth();

            // ZIS Collection Summary
            $zisCollection = ZisTransaction::where('status', 'verified')
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->selectRaw('
                    jenis_zis,
                    COUNT(*) as total_transactions,
                    SUM(jumlah) as total_amount
                ')
                ->groupBy('jenis_zis')
                ->get();

            // Distribution Summary
            $distributionSummary = Distribution::where('status', 'completed')
                ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
                ->with('program')
                ->selectRaw('
                    program_id,
                    COUNT(*) as total_distributions,
                    SUM(jumlah) as total_amount
                ')
                ->groupBy('program_id')
                ->get();

            // Monthly trends
            $monthlyTrends = ZisTransaction::where('status', 'verified')
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->selectRaw('
                    DATE_FORMAT(tanggal_transaksi, "%Y-%m") as month,
                    SUM(jumlah) as total_amount,
                    COUNT(*) as total_transactions
                ')
                ->groupBy(DB::raw('DATE_FORMAT(tanggal_transaksi, "%Y-%m")'))
                ->orderBy('month')
                ->get();

            // Top Donatur
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

            // Summary totals
            $totalCollection = ZisTransaction::where('status', 'verified')
                ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                ->sum('jumlah');

            $totalDistribution = Distribution::where('status', 'completed')
                ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
                ->sum('jumlah');

            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'summary' => [
                        'total_collection' => $totalCollection,
                        'total_distribution' => $totalDistribution,
                        'balance' => $totalCollection - $totalDistribution
                    ],
                    'zis_collection' => $zisCollection,
                    'distribution_summary' => $distributionSummary,
                    'monthly_trends' => $monthlyTrends,
                    'top_donatur' => $topDonatur
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil laporan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportCSV(Request $request)
    {
        try {
            $type = $request->type ?? 'zis'; // zis, distribution, donatur
            $startDate = $request->start_date ?? now()->startOfMonth();
            $endDate = $request->end_date ?? now()->endOfMonth();

            $filename = $type . '_report_' . date('Y-m-d') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($type, $startDate, $endDate) {
                $file = fopen('php://output', 'w');

                if ($type === 'zis') {
                    fputcsv($file, ['No Transaksi', 'Tanggal', 'Nama Donatur', 'Jenis ZIS', 'Jumlah', 'Status']);
                    
                    ZisTransaction::with('donatur')
                        ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                        ->chunk(1000, function($transactions) use ($file) {
                            foreach ($transactions as $transaction) {
                                fputcsv($file, [
                                    $transaction->nomor_transaksi,
                                    $transaction->tanggal_transaksi,
                                    $transaction->donatur->nama ?? '-',
                                    ucfirst($transaction->jenis_zis),
                                    number_format($transaction->jumlah, 0, ',', '.'),
                                    ucfirst($transaction->status)
                                ]);
                            }
                        });
                } elseif ($type === 'distribution') {
                    fputcsv($file, ['No Distribusi', 'Tanggal', 'Program', 'Mustahiq', 'Jumlah', 'Status']);
                    
                    Distribution::with(['program', 'mustahiq'])
                        ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
                        ->chunk(1000, function($distributions) use ($file) {
                            foreach ($distributions as $distribution) {
                                fputcsv($file, [
                                    $distribution->nomor_distribusi,
                                    $distribution->tanggal_distribusi,
                                    $distribution->program->nama ?? '-',
                                    $distribution->mustahiq->nama ?? '-',
                                    number_format($distribution->jumlah, 0, ',', '.'),
                                    ucfirst($distribution->status)
                                ]);
                            }
                        });
                } elseif ($type === 'donatur') {
                    fputcsv($file, ['Nama', 'NIK', 'Jenis', 'Alamat', 'Telepon', 'Email']);
                    
                    Donatur::chunk(1000, function($donaturs) use ($file) {
                        foreach ($donaturs as $donatur) {
                            fputcsv($file, [
                                $donatur->nama,
                                $donatur->nik,
                                ucfirst($donatur->jenis),
                                $donatur->alamat,
                                $donatur->telepon ?? '-',
                                $donatur->email ?? '-'
                            ]);
                        }
                    });
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengexport data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportPDF(Request $request)
    {
        try {
            $type = $request->type ?? 'zis'; // zis, distribution, donatur
            $startDate = $request->start_date ?? now()->startOfMonth();
            $endDate = $request->end_date ?? now()->endOfMonth();
            $user = auth()->user();

            $filename = $type . '_report_' . date('Y-m-d') . '.pdf';

            if ($type === 'zis') {
                $transactions = ZisTransaction::with(['donatur', 'upz'])
                    ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
                    ->orderBy('tanggal_transaksi', 'desc')
                    ->get();

                $pdf = Pdf::loadView('pdf.zis-transactions', compact('transactions', 'startDate', 'endDate', 'user'))
                    ->setPaper('a4', 'landscape');

            } elseif ($type === 'distribution') {
                $distributions = Distribution::with(['program', 'mustahiq'])
                    ->where('status', 'completed')
                    ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
                    ->orderBy('tanggal_distribusi', 'desc')
                    ->get();

                $pdf = Pdf::loadView('pdf.distributions', compact('distributions', 'startDate', 'endDate', 'user'))
                    ->setPaper('a4', 'landscape');

            } elseif ($type === 'donatur') {
                $jenis = $request->jenis;
                $query = Donatur::query();
                
                if ($jenis) {
                    $query->where('jenis', $jenis);
                }
                
                $donatur = $query->orderBy('nama')->get();

                $pdf = Pdf::loadView('pdf.donatur', compact('donatur', 'jenis', 'user'))
                    ->setPaper('a4', 'landscape');

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis laporan tidak valid'
                ], 400);
            }

            return $pdf->download($filename);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengexport PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function exportSummaryPDF(Request $request)
    {
        try {
            $startDate = $request->start_date ?? now()->startOfMonth();
            $endDate = $request->end_date ?? now()->endOfMonth();
            $user = auth()->user();

            // Get summary data
            $summaryData = $this->getSummaryData($startDate, $endDate);
            
            $pdf = Pdf::loadView('pdf.summary-report', compact('summaryData', 'startDate', 'endDate', 'user'))
                ->setPaper('a4', 'portrait');

            return $pdf->download('summary_report_' . date('Y-m-d') . '.pdf');

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengexport PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function getSummaryData($startDate, $endDate)
    {
        // ZIS Collection Summary
        $zisCollection = ZisTransaction::where('status', 'verified')
            ->whereBetween('tanggal_transaksi', [$startDate, $endDate])
            ->selectRaw('
                jenis_zis,
                COUNT(*) as total_transactions,
                SUM(jumlah) as total_amount
            ')
            ->groupBy('jenis_zis')
            ->get();

        // Distribution Summary
        $distributionSummary = Distribution::where('status', 'completed')
            ->whereBetween('tanggal_distribusi', [$startDate, $endDate])
            ->with('program')
            ->selectRaw('
                program_id,
                COUNT(*) as total_distributions,
                SUM(jumlah) as total_amount
            ')
            ->groupBy('program_id')
            ->get();

        // Top Donatur
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
            'zis_collection' => $zisCollection,
            'distribution_summary' => $distributionSummary,
            'top_donatur' => $topDonatur
        ];
    }
}