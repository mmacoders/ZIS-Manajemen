<?php

namespace App\Services;

use App\Models\ZisTransaction;
use App\Models\Muzakki;
use App\Models\Distribution;
use App\Models\Mustahiq;
use App\Models\Program;
use App\Models\MLAnalyticsPrediction;
use App\Models\MLLearningData;
use App\Models\MLAnalyticsCache;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MLAnalyticsService
{
    /**
     * Predict next month's donation amount using linear regression
     */
    public function predictNextMonthDonations(): array
    {
        // Get last 12 months of data
        $monthlyData = ZisTransaction::select(
            DB::raw('YEAR(tanggal_transaksi) as year'),
            DB::raw('MONTH(tanggal_transaksi) as month'),
            DB::raw('SUM(jumlah) as total_amount'),
            DB::raw('COUNT(*) as transaction_count')
        )
        ->where('tanggal_transaksi', '>=', Carbon::now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        if ($monthlyData->count() < 3) {
            return [
                'predicted_amount' => 0,
                'confidence' => 0,
                'trend' => 'insufficient_data',
                'message' => 'Insufficient data for prediction'
            ];
        }

        // Simple linear regression
        $x = [];
        $y = [];
        $n = $monthlyData->count();

        foreach ($monthlyData as $index => $data) {
            $x[] = $index + 1;
            $y[] = $data->total_amount;
        }

        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        // Predict next month
        $nextMonthX = $n + 1;
        $predictedAmount = $slope * $nextMonthX + $intercept;

        // Calculate confidence based on data consistency
        $avgAmount = $sumY / $n;
        $variance = 0;
        foreach ($y as $amount) {
            $variance += pow($amount - $avgAmount, 2);
        }
        $variance /= $n;
        $stdDev = sqrt($variance);
        $confidence = max(0, min(100, 100 - ($stdDev / $avgAmount * 100)));

        // Determine trend
        $trend = $slope > 0 ? 'increasing' : ($slope < 0 ? 'decreasing' : 'stable');

        return [
            'predicted_amount' => max(0, $predictedAmount),
            'confidence' => round($confidence, 2),
            'trend' => $trend,
            'historical_data' => $monthlyData,
            'slope' => $slope,
            'r_squared' => $this->calculateRSquared($x, $y, $slope, $intercept)
        ];
    }

    /**
     * Analyze donor behavior patterns
     */
    public function analyzeDonorPatterns(): array
    {
        $donors = Muzakki::with(['zisTransactions' => function($query) {
            $query->where('tanggal_transaksi', '>=', Carbon::now()->subYear());
        }])->get();

        $patterns = [
            'regular_donors' => [],
            'seasonal_donors' => [],
            'one_time_donors' => [],
            'high_value_donors' => [],
            'declining_donors' => []
        ];

        foreach ($donors as $donor) {
            $transactions = $donor->zisTransactions;
            
            if ($transactions->isEmpty()) {
                continue;
            }

            $totalAmount = $transactions->sum('jumlah');
            $transactionCount = $transactions->count();
            $avgAmount = $totalAmount / $transactionCount;
            $months = $transactions->groupBy(function($transaction) {
                return Carbon::parse($transaction->tanggal_transaksi)->format('Y-m');
            })->count();

            // Classify donor behavior
            if ($transactionCount >= 6 && $months >= 6) {
                $patterns['regular_donors'][] = [
                    'donor' => $donor,
                    'total_amount' => $totalAmount,
                    'frequency' => $transactionCount,
                    'avg_amount' => $avgAmount,
                    'consistency_score' => $this->calculateConsistencyScore($transactions)
                ];
            } elseif ($totalAmount > $this->getHighValueThreshold()) {
                $patterns['high_value_donors'][] = [
                    'donor' => $donor,
                    'total_amount' => $totalAmount,
                    'avg_amount' => $avgAmount,
                    'impact_score' => $this->calculateImpactScore($totalAmount)
                ];
            } elseif ($this->isSeasonalDonor($transactions)) {
                $patterns['seasonal_donors'][] = [
                    'donor' => $donor,
                    'seasonal_pattern' => $this->getSeasonalPattern($transactions),
                    'peak_months' => $this->getPeakMonths($transactions)
                ];
            } elseif ($transactionCount == 1) {
                $patterns['one_time_donors'][] = [
                    'donor' => $donor,
                    'amount' => $totalAmount,
                    'transaction_date' => $transactions->first()->tanggal_transaksi
                ];
            } elseif ($this->isDecliningDonor($transactions)) {
                $patterns['declining_donors'][] = [
                    'donor' => $donor,
                    'decline_rate' => $this->calculateDeclineRate($transactions),
                    'last_donation' => $transactions->sortByDesc('tanggal_transaksi')->first()
                ];
            }
        }

        return $patterns;
    }

    /**
     * Predict beneficiary needs using historical distribution data
     */
    public function predictBeneficiaryNeeds(): array
    {
        $beneficiaries = Mustahiq::with(['distributions' => function($query) {
            $query->where('tanggal_distribusi', '>=', Carbon::now()->subYear());
        }])->get();

        $predictions = [];
        $categoryNeeds = [];

        foreach ($beneficiaries as $beneficiary) {
            $distributions = $beneficiary->distributions;
            
            if ($distributions->isEmpty()) {
                continue;
            }

            $totalReceived = $distributions->sum('jumlah');
            $lastDistribution = $distributions->sortByDesc('tanggal_distribusi')->first();
            $daysSinceLastDistribution = Carbon::parse($lastDistribution->tanggal_distribusi)->diffInDays(Carbon::now());
            
            // Calculate need probability based on category and time since last distribution
            $needProbability = $this->calculateNeedProbability($beneficiary->kategori, $daysSinceLastDistribution, $totalReceived);
            
            if ($needProbability > 50) {
                $predictions[] = [
                    'beneficiary' => $beneficiary,
                    'need_probability' => $needProbability,
                    'suggested_amount' => $this->suggestDistributionAmount($beneficiary->kategori, $totalReceived),
                    'urgency_level' => $this->getUrgencyLevel($needProbability),
                    'days_since_last' => $daysSinceLastDistribution
                ];
            }

            // Aggregate by category
            if (!isset($categoryNeeds[$beneficiary->kategori])) {
                $categoryNeeds[$beneficiary->kategori] = [
                    'count' => 0,
                    'total_needed' => 0,
                    'avg_need_probability' => 0
                ];
            }
            
            $categoryNeeds[$beneficiary->kategori]['count']++;
            $categoryNeeds[$beneficiary->kategori]['total_needed'] += $this->suggestDistributionAmount($beneficiary->kategori, $totalReceived);
            $categoryNeeds[$beneficiary->kategori]['avg_need_probability'] += $needProbability;
        }

        // Calculate averages
        foreach ($categoryNeeds as $category => &$data) {
            $data['avg_need_probability'] = $data['avg_need_probability'] / $data['count'];
        }

        return [
            'individual_predictions' => collect($predictions)->sortByDesc('need_probability')->take(20)->values(),
            'category_analysis' => $categoryNeeds,
            'total_predicted_need' => collect($predictions)->sum('suggested_amount')
        ];
    }

    /**
     * Detect potential fraud patterns
     */
    public function detectFraudPatterns(): array
    {
        $suspiciousPatterns = [
            'duplicate_donors' => $this->detectDuplicateDonors(),
            'unusual_transactions' => $this->detectUnusualTransactions(),
            'suspicious_beneficiaries' => $this->detectSuspiciousBeneficiaries(),
            'timing_anomalies' => $this->detectTimingAnomalies()
        ];

        return $suspiciousPatterns;
    }

    /**
     * Generate comprehensive analytics dashboard data
     */
    public function generateAnalyticsDashboard(): array
    {
        return [
            'donation_prediction' => $this->predictNextMonthDonations(),
            'donor_patterns' => $this->analyzeDonorPatterns(),
            'beneficiary_needs' => $this->predictBeneficiaryNeeds(),
            'fraud_detection' => $this->detectFraudPatterns(),
            'performance_metrics' => $this->calculatePerformanceMetrics(),
            'recommendations' => $this->generateRecommendations(),
            'trend_analysis' => $this->analyzeTrends(),
            'efficiency_scores' => $this->calculateEfficiencyScores()
        ];
    }

    // Helper methods

    private function calculateRSquared(array $x, array $y, float $slope, float $intercept): float
    {
        $n = count($x);
        $yMean = array_sum($y) / $n;
        $ssTotal = 0;
        $ssRes = 0;

        for ($i = 0; $i < $n; $i++) {
            $yPred = $slope * $x[$i] + $intercept;
            $ssTotal += pow($y[$i] - $yMean, 2);
            $ssRes += pow($y[$i] - $yPred, 2);
        }

        return $ssTotal > 0 ? 1 - ($ssRes / $ssTotal) : 0;
    }

    private function calculateConsistencyScore(Collection $transactions): float
    {
        if ($transactions->count() < 2) return 0;

        $amounts = $transactions->pluck('jumlah')->toArray();
        $mean = array_sum($amounts) / count($amounts);
        $variance = 0;

        foreach ($amounts as $amount) {
            $variance += pow($amount - $mean, 2);
        }

        $variance /= count($amounts);
        $coefficientOfVariation = sqrt($variance) / $mean;

        return max(0, 100 - ($coefficientOfVariation * 100));
    }

    private function getHighValueThreshold(): int
    {
        $avgDonation = ZisTransaction::avg('jumlah') ?? 0;
        return $avgDonation * 3; // 3x average donation
    }

    private function calculateImpactScore(float $amount): float
    {
        $maxDonation = ZisTransaction::max('jumlah') ?? 1;
        return ($amount / $maxDonation) * 100;
    }

    private function isSeasonalDonor(Collection $transactions): bool
    {
        $monthCounts = [];
        foreach ($transactions as $transaction) {
            $month = Carbon::parse($transaction->tanggal_transaksi)->month;
            $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
        }

        $maxCount = max($monthCounts);
        $totalTransactions = $transactions->count();

        return $maxCount / $totalTransactions > 0.5; // More than 50% in one month
    }

    private function getSeasonalPattern(Collection $transactions): string
    {
        $monthCounts = [];
        foreach ($transactions as $transaction) {
            $month = Carbon::parse($transaction->tanggal_transaksi)->month;
            $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
        }

        $peakMonth = array_search(max($monthCounts), $monthCounts);
        
        if (in_array($peakMonth, [12, 1, 2])) return 'winter_peak';
        if (in_array($peakMonth, [6, 7, 8])) return 'ramadan_peak';
        if (in_array($peakMonth, [3, 4, 5])) return 'spring_peak';
        return 'autumn_peak';
    }

    private function getPeakMonths(Collection $transactions): array
    {
        $monthCounts = [];
        foreach ($transactions as $transaction) {
            $month = Carbon::parse($transaction->tanggal_transaksi)->month;
            $monthCounts[$month] = ($monthCounts[$month] ?? 0) + 1;
        }

        arsort($monthCounts);
        return array_keys(array_slice($monthCounts, 0, 3, true));
    }

    private function isDecliningDonor(Collection $transactions): bool
    {
        if ($transactions->count() < 3) return false;

        $sortedTransactions = $transactions->sortBy('tanggal_transaksi');
        $recentHalf = $sortedTransactions->slice(-ceil($transactions->count() / 2));
        $earlierHalf = $sortedTransactions->slice(0, floor($transactions->count() / 2));

        $recentAvg = $recentHalf->avg('jumlah');
        $earlierAvg = $earlierHalf->avg('jumlah');

        return $recentAvg < $earlierAvg * 0.7; // 30% decline
    }

    private function calculateDeclineRate(Collection $transactions): float
    {
        $sortedTransactions = $transactions->sortBy('tanggal_transaksi');
        $recentHalf = $sortedTransactions->slice(-ceil($transactions->count() / 2));
        $earlierHalf = $sortedTransactions->slice(0, floor($transactions->count() / 2));

        $recentAvg = $recentHalf->avg('jumlah');
        $earlierAvg = $earlierHalf->avg('jumlah');

        return $earlierAvg > 0 ? (($earlierAvg - $recentAvg) / $earlierAvg) * 100 : 0;
    }

    private function calculateNeedProbability(string $category, int $daysSinceLastDistribution, float $totalReceived): float
    {
        $categoryWeights = [
            'fakir' => 0.9,
            'miskin' => 0.8,
            'amil' => 0.3,
            'muallaf' => 0.6,
            'riqab' => 0.7,
            'gharim' => 0.8,
            'fisabilillah' => 0.5,
            'ibnu_sabil' => 0.6
        ];

        $baseWeight = $categoryWeights[$category] ?? 0.5;
        $timeWeight = min(1.0, $daysSinceLastDistribution / 90); // 90 days = full weight
        $amountWeight = $totalReceived < 1000000 ? 0.8 : 0.6; // Lower amounts = higher need

        return ($baseWeight * 0.5 + $timeWeight * 0.3 + $amountWeight * 0.2) * 100;
    }

    private function suggestDistributionAmount(string $category, float $previousTotal): int
    {
        $baseSuggestions = [
            'fakir' => 1500000,
            'miskin' => 1000000,
            'amil' => 500000,
            'muallaf' => 750000,
            'riqab' => 800000,
            'gharim' => 1200000,
            'fisabilillah' => 600000,
            'ibnu_sabil' => 700000
        ];

        $baseAmount = $baseSuggestions[$category] ?? 500000;
        
        // Adjust based on previous distributions
        if ($previousTotal > 0) {
            $avgPrevious = $previousTotal / 4; // Assume 4 distributions per year
            $suggestedAmount = ($baseAmount + $avgPrevious) / 2;
        } else {
            $suggestedAmount = $baseAmount;
        }

        return (int) $suggestedAmount;
    }

    private function getUrgencyLevel(float $probability): string
    {
        if ($probability >= 80) return 'high';
        if ($probability >= 60) return 'medium';
        if ($probability >= 40) return 'low';
        return 'very_low';
    }

    private function detectDuplicateDonors(): array
    {
        return Muzakki::select('nik', DB::raw('COUNT(*) as count'))
            ->groupBy('nik')
            ->having('count', '>', 1)
            ->with('zisTransactions')
            ->get()
            ->toArray();
    }

    private function detectUnusualTransactions(): array
    {
        $avgAmount = ZisTransaction::avg('jumlah');
        $threshold = $avgAmount * 10; // 10x average is unusual

        return ZisTransaction::where('jumlah', '>', $threshold)
            ->with(['muzakki'])
            ->get()
            ->map(function($transaction) use ($avgAmount) {
                $transaction->anomaly_score = $transaction->jumlah / $avgAmount;
                return $transaction;
            })
            ->toArray();
    }

    private function detectSuspiciousBeneficiaries(): array
    {
        // Beneficiaries receiving unusually frequent distributions
        return Mustahiq::withCount(['distributions' => function($query) {
                $query->where('tanggal_distribusi', '>=', Carbon::now()->subMonths(6));
            }])
            ->having('distributions_count', '>', 5)
            ->with('distributions')
            ->get()
            ->toArray();
    }

    private function detectTimingAnomalies(): array
    {
        // Transactions occurring at unusual times
        return ZisTransaction::whereTime('created_at', '<', '06:00:00')
            ->orWhereTime('created_at', '>', '22:00:00')
            ->with('muzakki')
            ->get()
            ->toArray();
    }

    private function calculatePerformanceMetrics(): array
    {
        $totalDonations = ZisTransaction::sum('jumlah');
        $totalDistributions = Distribution::sum('jumlah');
        $efficiency = $totalDonations > 0 ? ($totalDistributions / $totalDonations) * 100 : 0;

        return [
            'total_donations' => $totalDonations,
            'total_distributions' => $totalDistributions,
            'efficiency_percentage' => $efficiency,
            'active_donors_count' => Muzakki::whereHas('zisTransactions', function($query) {
                $query->where('tanggal_transaksi', '>=', Carbon::now()->subMonths(6));
            })->count(),
            'active_beneficiaries_count' => Mustahiq::whereHas('distributions', function($query) {
                $query->where('tanggal_distribusi', '>=', Carbon::now()->subMonths(6));
            })->count()
        ];
    }

    private function generateRecommendations(): array
    {
        $recommendations = [];
        
        // Donor retention recommendations
        $patterns = $this->analyzeDonorPatterns();
        if (count($patterns['declining_donors']) > 0) {
            $recommendations[] = [
                'type' => 'donor_retention',
                'priority' => 'high',
                'message' => 'Focus on re-engaging ' . count($patterns['declining_donors']) . ' declining donors',
                'action' => 'Create targeted communication campaign'
            ];
        }

        // Distribution efficiency recommendations
        $metrics = $this->calculatePerformanceMetrics();
        if ($metrics['efficiency_percentage'] < 80) {
            $recommendations[] = [
                'type' => 'efficiency',
                'priority' => 'medium',
                'message' => 'Distribution efficiency is below optimal (80%)',
                'action' => 'Review distribution processes and reduce administrative costs'
            ];
        }

        return $recommendations;
    }

    private function analyzeTrends(): array
    {
        $monthlyTrends = ZisTransaction::select(
            DB::raw('YEAR(tanggal_transaksi) as year'),
            DB::raw('MONTH(tanggal_transaksi) as month'),
            DB::raw('SUM(jumlah) as total_amount'),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('AVG(jumlah) as avg_amount')
        )
        ->where('tanggal_transaksi', '>=', Carbon::now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();

        return [
            'monthly_trends' => $monthlyTrends,
            'growth_rate' => $this->calculateGrowthRate($monthlyTrends),
            'seasonality' => $this->detectSeasonality($monthlyTrends)
        ];
    }

    private function calculateGrowthRate(Collection $monthlyData): float
    {
        if ($monthlyData->count() < 2) return 0;

        $first = $monthlyData->first();
        $last = $monthlyData->last();

        return $first->total_amount > 0 ? 
            (($last->total_amount - $first->total_amount) / $first->total_amount) * 100 : 0;
    }

    private function detectSeasonality(Collection $monthlyData): array
    {
        $monthlyAverages = [];
        
        foreach ($monthlyData as $data) {
            $month = $data->month;
            if (!isset($monthlyAverages[$month])) {
                $monthlyAverages[$month] = [];
            }
            $monthlyAverages[$month][] = $data->total_amount;
        }

        $seasonalPattern = [];
        foreach ($monthlyAverages as $month => $amounts) {
            $seasonalPattern[$month] = array_sum($amounts) / count($amounts);
        }

        return $seasonalPattern;
    }

    private function calculateEfficiencyScores(): array
    {
        return [
            'donation_collection_efficiency' => $this->calculateDonationEfficiency(),
            'distribution_efficiency' => $this->calculateDistributionEfficiency(),
            'beneficiary_satisfaction_score' => $this->calculateSatisfactionScore(),
            'resource_utilization_score' => $this->calculateResourceUtilization()
        ];
    }

    private function calculateDonationEfficiency(): float
    {
        $totalDonors = Muzakki::count();
        $activeDonors = Muzakki::whereHas('zisTransactions', function($query) {
            $query->where('tanggal_transaksi', '>=', Carbon::now()->subMonths(12));
        })->count();

        return $totalDonors > 0 ? ($activeDonors / $totalDonors) * 100 : 0;
    }

    private function calculateDistributionEfficiency(): float
    {
        $totalBeneficiaries = Mustahiq::where('status', 'aktif')->count();
        $servedBeneficiaries = Mustahiq::whereHas('distributions', function($query) {
            $query->where('tanggal_distribusi', '>=', Carbon::now()->subMonths(12));
        })->count();

        return $totalBeneficiaries > 0 ? ($servedBeneficiaries / $totalBeneficiaries) * 100 : 0;
    }

    private function calculateSatisfactionScore(): float
    {
        // Simulated satisfaction score based on distribution frequency and amounts
        $recentDistributions = Distribution::where('tanggal_distribusi', '>=', Carbon::now()->subMonths(6))->count();
        $targetDistributions = Mustahiq::where('status', 'aktif')->count() * 2; // Target: 2 distributions per beneficiary per 6 months

        return $targetDistributions > 0 ? min(100, ($recentDistributions / $targetDistributions) * 100) : 0;
    }

    private function calculateResourceUtilization(): float
    {
        $totalDonations = ZisTransaction::where('tanggal_transaksi', '>=', Carbon::now()->subMonths(12))->sum('jumlah');
        $totalDistributions = Distribution::where('tanggal_distribusi', '>=', Carbon::now()->subMonths(12))->sum('jumlah');

        return $totalDonations > 0 ? ($totalDistributions / $totalDonations) * 100 : 0;
    }

    /**
     * Get model statistics and performance metrics
     */
    public function getModelStatistics(): array
    {
        return [
            'data_statistics' => $this->getDataStatistics(),
            'model_performance' => $this->getModelPerformance(),
            'system_health' => $this->getSystemHealthMetrics(),
            'learning_progress' => $this->getLearningProgress()
        ];
    }

    private function getDataStatistics(): array
    {
        return [
            'total_transactions' => ZisTransaction::count(),
            'total_donors' => Muzakki::count(),
            'total_beneficiaries' => Mustahiq::count(),
            'total_distributions' => Distribution::count(),
            'total_programs' => Program::count(),
            'data_coverage_days' => $this->calculateDataCoverage()
        ];
    }

    private function getModelPerformance(): array
    {
        // Get verified predictions to calculate accuracy
        $verifiedPredictions = MLAnalyticsPrediction::whereNotNull('actual_value')->get();
        
        $accuracyMetrics = [
            'total_verified' => $verifiedPredictions->count(),
            'average_error_margin' => 0,
            'accuracy_percentage' => 0
        ];

        if ($verifiedPredictions->count() > 0) {
            $totalError = 0;
            $accuratePredictions = 0;

            foreach ($verifiedPredictions as $prediction) {
                $predicted = $prediction->prediction_result['predicted_amount'] ?? 0;
                $actual = $prediction->actual_value;

                if ($actual > 0) {
                    $errorMargin = abs(($predicted - $actual) / $actual) * 100;
                    $totalError += $errorMargin;
                    
                    // Consider prediction accurate if error is less than 20%
                    if ($errorMargin < 20) {
                        $accuratePredictions++;
                    }
                }
            }

            $accuracyMetrics['average_error_margin'] = $totalError / $verifiedPredictions->count();
            $accuracyMetrics['accuracy_percentage'] = ($accuratePredictions / $verifiedPredictions->count()) * 100;
        }

        return $accuracyMetrics;
    }

    private function getSystemHealthMetrics(): array
    {
        return [
            'cache_hit_rate' => $this->calculateCacheHitRate(),
            'data_freshness_hours' => $this->calculateDataFreshness(),
            'system_uptime_percentage' => 99.9, // Placeholder - would come from monitoring system
            'last_training_update' => $this->getLastTrainingUpdate()
        ];
    }

    private function getLearningProgress(): array
    {
        $totalLearningData = MLLearningData::count();
        $validatedData = MLLearningData::where('is_validated', true)->count();
        
        return [
            'total_learning_samples' => $totalLearningData,
            'validated_samples' => $validatedData,
            'validation_rate' => $totalLearningData > 0 ? ($validatedData / $totalLearningData) * 100 : 0,
            'last_model_update' => $this->getLastModelUpdate()
        ];
    }

    private function calculateDataCoverage(): int
    {
        $firstTransaction = ZisTransaction::orderBy('tanggal_transaksi', 'asc')->first();
        if (!$firstTransaction) {
            return 0;
        }
        
        return now()->diffInDays($firstTransaction->tanggal_transaksi);
    }

    private function calculateCacheHitRate(): float
    {
        $totalRequests = MLAnalyticsCache::sum('hit_count');
        $cacheEntries = MLAnalyticsCache::count();
        
        return $cacheEntries > 0 ? ($totalRequests / $cacheEntries) : 0;
    }

    private function calculateDataFreshness(): int
    {
        $lastTransaction = ZisTransaction::orderBy('tanggal_transaksi', 'desc')->first();
        if (!$lastTransaction) {
            return 0;
        }
        
        return now()->diffInHours($lastTransaction->tanggal_transaksi);
    }

    private function getLastTrainingUpdate(): ?string
    {
        $lastLearningData = MLLearningData::orderBy('created_at', 'desc')->first();
        return $lastLearningData ? $lastLearningData->created_at->toISOString() : null;
    }

    private function getLastModelUpdate(): ?string
    {
        $lastPrediction = MLAnalyticsPrediction::orderBy('created_at', 'desc')->first();
        return $lastPrediction ? $lastPrediction->created_at->toISOString() : null;
    }
}