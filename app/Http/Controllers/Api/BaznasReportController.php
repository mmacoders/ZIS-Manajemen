<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaznasReport;
use App\Models\ShariaTransaction;
use App\Models\ShariaFundCategory;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class BaznasReportController extends Controller
{
    /**
     * Get all BAZNAS reports with filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $year = $request->input('year');
            $type = $request->input('type');
            $status = $request->input('status');
            
            $query = BaznasReport::with(['preparer:id,name', 'approver:id,name']);
            
            if ($year) {
                $query->byYear($year);
            }
            
            if ($type) {
                $query->byType($type);
            }
            
            if ($status) {
                $query->byStatus($status);
            }
            
            $reports = $query->orderBy('report_year', 'desc')
                ->orderBy('period_start', 'desc')
                ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $reports
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching BAZNAS reports',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get specific BAZNAS report
     */
    public function show($id): JsonResponse
    {
        try {
            $report = BaznasReport::with(['preparer:id,name', 'approver:id,name'])
                ->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $report
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching BAZNAS report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create new BAZNAS report
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'report_type' => 'required|in:monthly,quarterly,annual,special',
                'report_year' => 'required|integer|min:2020|max:' . (date('Y') + 1),
                'report_period' => 'required|string',
                'period_start' => 'required|date',
                'period_end' => 'required|date|after:period_start'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            // Check if report already exists for this period
            $existingReport = BaznasReport::where('report_type', $request->report_type)
                ->where('report_year', $request->report_year)
                ->where('report_period', $request->report_period)
                ->first();
            
            if ($existingReport) {
                return response()->json([
                    'success' => false,
                    'message' => 'Report already exists for this period'
                ], 409);
            }
            
            $reportCode = BaznasReport::generateReportCode(
                $request->report_type,
                $request->report_year,
                $request->report_period
            );
            
            $report = BaznasReport::create([
                'report_code' => $reportCode,
                'report_type' => $request->report_type,
                'report_period' => $request->report_period,
                'report_year' => $request->report_year,
                'period_start' => $request->period_start,
                'period_end' => $request->period_end,
                'status' => 'draft',
                'prepared_by' => auth()->id()
            ]);
            
            // Calculate report data
            $report->calculateReportData();
            $report->generateExecutiveSummary();
            $report->generateRecommendations();
            
            return response()->json([
                'success' => true,
                'message' => 'BAZNAS report created successfully',
                'data' => $report->load('preparer')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating BAZNAS report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Update BAZNAS report
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $report = BaznasReport::findOrFail($id);
            
            if ($report->status === 'submitted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot update submitted report'
                ], 400);
            }
            
            $validator = Validator::make($request->all(), [
                'executive_summary' => 'sometimes|string',
                'recommendations' => 'sometimes|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $report->update($request->only([
                'executive_summary',
                'recommendations'
            ]));
            
            return response()->json([
                'success' => true,
                'message' => 'BAZNAS report updated successfully',
                'data' => $report
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating BAZNAS report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Approve BAZNAS report
     */
    public function approve($id): JsonResponse
    {
        try {
            $report = BaznasReport::findOrFail($id);
            
            if ($report->approve(auth()->id())) {
                return response()->json([
                    'success' => true,
                    'message' => 'BAZNAS report approved successfully',
                    'data' => $report->load(['preparer', 'approver'])
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Report cannot be approved in current status'
                ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error approving BAZNAS report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Submit BAZNAS report
     */
    public function submit($id): JsonResponse
    {
        try {
            $report = BaznasReport::findOrFail($id);
            
            if ($report->submit()) {
                return response()->json([
                    'success' => true,
                    'message' => 'BAZNAS report submitted successfully',
                    'data' => $report
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Report must be approved before submission'
                ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting BAZNAS report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Generate PDF report
     */
    public function generatePdf($id): JsonResponse
    {
        try {
            $report = BaznasReport::findOrFail($id);
            
            // Prepare data for PDF
            $data = [
                'report' => $report,
                'period_name' => $this->getPeriodName($report->report_period),
                'generated_at' => now()->format('d/m/Y H:i:s'),
                'generated_by' => auth()->user()->name
            ];
            
            // Generate PDF
            $pdf = Pdf::loadView('reports.baznas-report', $data)
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'DejaVu Sans'
                ]);
            
            // Save PDF file
            $fileName = "baznas_report_{$report->report_code}_" . now()->format('Ymd_His') . ".pdf";
            $filePath = "reports/baznas/{$fileName}";
            
            Storage::disk('public')->put($filePath, $pdf->output());
            
            // Update report with file path
            $report->update(['file_path' => $filePath]);
            
            return response()->json([
                'success' => true,
                'message' => 'PDF report generated successfully',
                'data' => [
                    'file_path' => $filePath,
                    'download_url' => Storage::disk('public')->url($filePath),
                    'file_name' => $fileName
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating PDF report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download PDF report
     */
    public function downloadPdf($id)
    {
        try {
            $report = BaznasReport::findOrFail($id);
            
            if (!$report->file_path || !Storage::disk('public')->exists($report->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PDF file not found. Please generate the report first.'
                ], 404);
            }
            
            $fileName = "BAZNAS_Report_{$report->report_code}.pdf";
            
            return Storage::disk('public')->download($report->file_path, $fileName);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error downloading PDF report',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get BAZNAS compliance dashboard
     */
    public function complianceDashboard(): JsonResponse
    {
        try {
            $currentYear = Carbon::now()->year;
            
            // Get yearly compliance metrics
            $yearlyMetrics = [];
            for ($year = $currentYear - 2; $year <= $currentYear; $year++) {
                $startDate = Carbon::create($year)->startOfYear();
                $endDate = Carbon::create($year)->endOfYear();
                
                $totalCollection = ShariaTransaction::byDateRange($startDate, $endDate)
                    ->byType('collection')
                    ->sum('amount');
                    
                $totalDistribution = ShariaTransaction::byDateRange($startDate, $endDate)
                    ->byType('distribution')
                    ->sum('amount');
                    
                $totalAmil = ShariaTransaction::byDateRange($startDate, $endDate)
                    ->sum('amil_amount');
                
                $yearlyMetrics[] = [
                    'year' => $year,
                    'total_collection' => $totalCollection,
                    'total_distribution' => $totalDistribution,
                    'total_amil' => $totalAmil,
                    'amil_percentage' => $totalCollection > 0 ? ($totalAmil / $totalCollection) * 100 : 0,
                    'distribution_efficiency' => $totalCollection > 0 ? ($totalDistribution / $totalCollection) * 100 : 0
                ];
            }
            
            // Get recent reports status
            $recentReports = BaznasReport::with('preparer')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'report_code' => $report->report_code,
                        'report_type' => $report->report_type,
                        'report_period' => $report->report_period,
                        'report_year' => $report->report_year,
                        'status' => $report->status,
                        'compliance_rate' => $report->compliance_metrics['baznas_compliance_rate'] ?? 100,
                        'amil_percentage' => $report->compliance_metrics['amil_percentage'] ?? 0,
                        'created_at' => $report->created_at
                    ];
                });
            
            // Get fund balances
            $fundBalances = ShariaFundCategory::active()
                ->get()
                ->map(function ($category) {
                    return [
                        'category' => $category->name,
                        'type' => $category->type,
                        'balance' => $category->getTotalBalance(),
                        'is_compliant' => $category->isBaznasCompliant()
                    ];
                });
            
            // Calculate overall compliance score
            $overallCompliance = $this->calculateOverallCompliance();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'yearly_metrics' => $yearlyMetrics,
                    'recent_reports' => $recentReports,
                    'fund_balances' => $fundBalances,
                    'overall_compliance' => $overallCompliance
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching compliance dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get period name for display
     */
    private function getPeriodName(string $period): string
    {
        $periodNames = [
            'january' => 'Januari',
            'february' => 'Februari',
            'march' => 'Maret',
            'april' => 'April',
            'may' => 'Mei',
            'june' => 'Juni',
            'july' => 'Juli',
            'august' => 'Agustus',
            'september' => 'September',
            'october' => 'Oktober',
            'november' => 'November',
            'december' => 'Desember',
            'q1' => 'Kuartal 1',
            'q2' => 'Kuartal 2',
            'q3' => 'Kuartal 3',
            'q4' => 'Kuartal 4',
            'annual' => 'Tahunan'
        ];
        
        return $periodNames[$period] ?? ucfirst($period);
    }
    
    /**
     * Calculate overall compliance score
     */
    private function calculateOverallCompliance(): array
    {
        $currentYear = Carbon::now()->startOfYear();
        
        // Check amil compliance
        $totalCollection = ShariaTransaction::byDateRange($currentYear, now())
            ->byType('collection')
            ->sum('amount');
            
        $totalAmil = ShariaTransaction::byDateRange($currentYear, now())
            ->sum('amil_amount');
            
        $amilPercentage = $totalCollection > 0 ? ($totalAmil / $totalCollection) * 100 : 0;
        $amilCompliant = $amilPercentage <= 12.5;
        
        // Check fund category compliance
        $totalCategories = ShariaFundCategory::active()->count();
        $compliantCategories = ShariaFundCategory::active()->baznasCompliant()->count();
        $categoryCompliance = $totalCategories > 0 ? ($compliantCategories / $totalCategories) * 100 : 100;
        
        // Check transaction compliance
        $totalTransactions = ShariaTransaction::byDateRange($currentYear, now())->count();
        $compliantTransactions = ShariaTransaction::byDateRange($currentYear, now())
            ->baznasCompliant()
            ->count();
        $transactionCompliance = $totalTransactions > 0 ? ($compliantTransactions / $totalTransactions) * 100 : 100;
        
        // Calculate overall score
        $overallScore = ($categoryCompliance + $transactionCompliance + ($amilCompliant ? 100 : 0)) / 3;
        
        return [
            'overall_score' => round($overallScore, 2),
            'amil_compliance' => [
                'percentage' => round($amilPercentage, 2),
                'is_compliant' => $amilCompliant,
                'standard' => 12.5
            ],
            'category_compliance' => round($categoryCompliance, 2),
            'transaction_compliance' => round($transactionCompliance, 2),
            'status' => $overallScore >= 90 ? 'excellent' : ($overallScore >= 80 ? 'good' : ($overallScore >= 70 ? 'fair' : 'needs_improvement'))
        ];
    }
}
