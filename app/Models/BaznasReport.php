<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class BaznasReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'report_year',
        'report_period',
        'period_start',
        'period_end',
        'total_collection',
        'total_zakat',
        'total_infaq',
        'total_sedekah',
        'total_amil',
        'total_distribution',
        'asnaf_distribution',
        'geographical_distribution',
        'collection_sources',
        'compliance_metrics',
        'opening_balance',
        'closing_balance',
        'executive_summary',
        'recommendations',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'created_by'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_collection' => 'decimal:2',
        'total_zakat' => 'decimal:2',
        'total_infaq' => 'decimal:2',
        'total_sedekah' => 'decimal:2',
        'total_amil' => 'decimal:2',
        'total_distribution' => 'decimal:2',
        'asnaf_distribution' => 'array',
        'geographical_distribution' => 'array',
        'collection_sources' => 'array',
        'compliance_metrics' => 'array',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Relationship to creator
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to approver
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate report data
     */
    public function generateReportData(): void
    {
        $startDate = $this->period_start;
        $endDate = $this->period_end;

        // Get all transactions in the period
        $transactions = ShariaTransaction::byDateRange($startDate, $endDate)
            ->posted()
            ->baznasCompliant()
            ->with(['fundCategory', 'donatur', 'mustahiq'])
            ->get();

        // Calculate collections by fund type
        $this->total_collection = $transactions->where('transaction_type', 'collection')->sum('amount');
        $this->total_zakat = $transactions->where('transaction_type', 'collection')
            ->whereHas('fundCategory', fn($q) => $q->where('type', 'zakat'))
            ->sum('amount');
        $this->total_infaq = $transactions->where('transaction_type', 'collection')
            ->whereHas('fundCategory', fn($q) => $q->where('type', 'infaq'))
            ->sum('amount');
        $this->total_sedekah = $transactions->where('transaction_type', 'collection')
            ->whereHas('fundCategory', fn($q) => $q->where('type', 'sedekah'))
            ->sum('amount');
        
        // Calculate amil and distribution
        $this->total_amil = $transactions->sum('amil_amount');
        $this->total_distribution = $transactions->where('transaction_type', 'distribution')->sum('amount');

        // Calculate asnaf distribution
        $asnafDistribution = [];
        $asnafCategories = ['fakir', 'miskin', 'amil', 'muallaf', 'riqab', 'gharim', 'fisabilillah', 'ibnu_sabil'];
        
        foreach ($asnafCategories as $category) {
            $asnafDistribution[$category] = $transactions
                ->where('transaction_type', 'distribution')
                ->where('mustahiq_category', $category)
                ->sum('amount');
        }
        $this->asnaf_distribution = $asnafDistribution;

        // Calculate geographical distribution (basic example)
        $geographicalDistribution = $transactions
            ->where('transaction_type', 'distribution')
            ->whereNotNull('mustahiq_id')
            ->groupBy(function($transaction) {
                return $transaction->mustahiq->provinsi ?? 'Unknown';
            })
            ->map(function($group) {
                return $group->sum('amount');
            })
            ->toArray();
        $this->geographical_distribution = $geographicalDistribution;

        // Calculate collection sources
        $collectionSources = [
            'individual' => $transactions->where('transaction_type', 'collection')
                ->whereHas('donatur', fn($q) => $q->where('jenis', 'individu'))
                ->sum('amount'),
            'corporate' => $transactions->where('transaction_type', 'collection')
                ->whereHas('donatur', fn($q) => $q->where('jenis', 'perusahaan'))
                ->sum('amount')
        ];
        $this->collection_sources = $collectionSources;

        // Calculate compliance metrics
        $totalAmilPercentage = $this->total_collection > 0 
            ? ($this->total_amil / $this->total_collection) * 100 
            : 0;
        
        $distributionEfficiency = $this->total_collection > 0 
            ? ($this->total_distribution / $this->total_collection) * 100 
            : 0;

        $this->compliance_metrics = [
            'amil_percentage' => round($totalAmilPercentage, 2),
            'distribution_efficiency' => round($distributionEfficiency, 2),
            'baznas_compliance_rate' => 100, // All included transactions are compliant
            'total_donatur' => $transactions->where('transaction_type', 'collection')
                ->distinct('donatur_id')->count('donatur_id'),
            'total_mustahiq' => $transactions->where('transaction_type', 'distribution')
                ->distinct('mustahiq_id')->count('mustahiq_id')
        ];

        // Calculate balances
        $this->opening_balance = $this->calculateOpeningBalance();
        $this->closing_balance = $this->opening_balance + $this->total_collection - $this->total_distribution;

        $this->save();
    }

    /**
     * Calculate opening balance for the period
     */
    private function calculateOpeningBalance(): float
    {
        // Get balance from previous period's closing or from account balances
        $previousReport = self::where('report_year', $this->report_year)
            ->where('period_end', '<', $this->period_start)
            ->orderBy('period_end', 'desc')
            ->first();

        return $previousReport ? $previousReport->closing_balance : 0;
    }

    /**
     * Generate executive summary
     */
    public function generateExecutiveSummary(): void
    {
        $metrics = $this->compliance_metrics;
        $period = Carbon::parse($this->period_start)->format('F Y');
        
        $summary = "Laporan BAZNAS periode {$period}:\n\n";
        $summary .= "1. Total Pengumpulan: Rp " . number_format($this->total_collection, 0, ',', '.') . "\n";
        $summary .= "   - Zakat: Rp " . number_format($this->total_zakat, 0, ',', '.') . "\n";
        $summary .= "   - Infaq: Rp " . number_format($this->total_infaq, 0, ',', '.') . "\n";
        $summary .= "   - Sedekah: Rp " . number_format($this->total_sedekah, 0, ',', '.') . "\n\n";
        
        $summary .= "2. Total Distribusi: Rp " . number_format($this->total_distribution, 0, ',', '.') . "\n";
        $summary .= "   - Efisiensi Distribusi: {$metrics['distribution_efficiency']}%\n\n";
        
        $summary .= "3. Amil: Rp " . number_format($this->total_amil, 0, ',', '.') . "\n";
        $summary .= "   - Persentase Amil: {$metrics['amil_percentage']}% (Standar BAZNAS: ≤12.5%)\n\n";
        
        $summary .= "4. Jumlah Donatur: {$metrics['total_donatur']} orang\n";
        $summary .= "5. Jumlah Mustahiq: {$metrics['total_mustahiq']} orang\n\n";
        
        $summary .= "6. Tingkat Kepatuhan BAZNAS: {$metrics['baznas_compliance_rate']}%";
        
        $this->executive_summary = $summary;
        $this->save();
    }

    /**
     * Generate recommendations
     */
    public function generateRecommendations(): void
    {
        $recommendations = [];
        $metrics = $this->compliance_metrics;
        
        // Check amil percentage
        if ($metrics['amil_percentage'] > 12.5) {
            $recommendations[] = "Persentase amil ({$metrics['amil_percentage']}%) melebihi standar BAZNAS (12.5%). Disarankan untuk meninjau alokasi amil.";
        }
        
        // Check distribution efficiency
        if ($metrics['distribution_efficiency'] < 80) {
            $recommendations[] = "Efisiensi distribusi ({$metrics['distribution_efficiency']}%) masih rendah. Disarankan untuk meningkatkan program distribusi.";
        }
        
        // Check balance accumulation
        if ($this->closing_balance > ($this->total_collection * 0.2)) {
            $recommendations[] = "Saldo akhir periode cukup tinggi. Disarankan untuk meningkatkan program distribusi atau mengembangkan program baru.";
        }
        
        // Asnaf distribution check
        $asnafTotal = array_sum($this->asnaf_distribution);
        $lowDistributionAsnaf = [];
        foreach ($this->asnaf_distribution as $asnaf => $amount) {
            if ($asnafTotal > 0 && ($amount / $asnafTotal) < 0.05) { // Less than 5%
                $lowDistributionAsnaf[] = $asnaf;
            }
        }
        
        if (!empty($lowDistributionAsnaf)) {
            $recommendations[] = "Distribusi untuk asnaf " . implode(', ', $lowDistributionAsnaf) . " masih rendah. Disarankan untuk mengembangkan program khusus.";
        }
        
        if (empty($recommendations)) {
            $recommendations[] = "Kinerja organisasi sudah baik dan sesuai dengan standar BAZNAS. Pertahankan konsistensi dan tingkatkan inovasi program.";
        }
        
        $this->recommendations = implode("\n\n", $recommendations);
        $this->save();
    }

    /**
     * Approve report
     */
    public function approve(int $approverId): bool
    {
        if ($this->status !== 'review') {
            return false;
        }
        
        $this->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now()
        ]);
        
        return true;
    }

    /**
     * Submit report to BAZNAS
     */
    public function submit(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }
        
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now()
        ]);
        
        // In a real implementation, this would send the report to BAZNAS
        // For now, we'll just update the status
        
        return true;
    }

    /**
     * Get report as PDF
     */
    public function generatePdf()
    {
        // This would generate a PDF report
        // Implementation would depend on your PDF generation library
        // For example, using Barryvdh\DomPDF\Facades\Pdf
        
        // return Pdf::loadView('pdf.baznas-report', ['report' => $this])
        //     ->setPaper('a4', 'portrait')
        //     ->download('baznas-report-' . $this->id . '.pdf');
    }
}