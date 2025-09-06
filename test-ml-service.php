<?php

require_once 'vendor/autoload.php';

use App\Services\MLAnalyticsService;

// Create a new instance of the MLAnalyticsService
$mlService = new MLAnalyticsService();

try {
    echo "Testing ML Analytics Service...\n";
    
    // Test the generateAnalyticsDashboard method
    echo "Generating analytics dashboard...\n";
    $dashboardData = $mlService->generateAnalyticsDashboard();
    
    echo "Dashboard data generated successfully!\n";
    echo "Keys in dashboard data: " . implode(', ', array_keys($dashboardData)) . "\n";
    
    // Test individual methods
    echo "\nTesting individual methods...\n";
    
    echo "Testing predictNextMonthDonations...\n";
    $donationPredictions = $mlService->predictNextMonthDonations();
    echo "Donation predictions generated successfully!\n";
    
    echo "Testing analyzeDonorPatterns...\n";
    $donorPatterns = $mlService->analyzeDonorPatterns();
    echo "Donor patterns analyzed successfully!\n";
    
    echo "Testing predictBeneficiaryNeeds...\n";
    $beneficiaryNeeds = $mlService->predictBeneficiaryNeeds();
    echo "Beneficiary needs predicted successfully!\n";
    
    echo "Testing detectFraudPatterns...\n";
    $fraudPatterns = $mlService->detectFraudPatterns();
    echo "Fraud patterns detected successfully!\n";
    
    echo "Testing calculatePerformanceMetrics...\n";
    $performanceMetrics = $mlService->calculatePerformanceMetrics();
    echo "Performance metrics calculated successfully!\n";
    
    echo "Testing generateRecommendations...\n";
    $recommendations = $mlService->generateRecommendations();
    echo "Recommendations generated successfully!\n";
    
    echo "Testing analyzeTrends...\n";
    $trends = $mlService->analyzeTrends();
    echo "Trends analyzed successfully!\n";
    
    echo "Testing calculateEfficiencyScores...\n";
    $efficiencyScores = $mlService->calculateEfficiencyScores();
    echo "Efficiency scores calculated successfully!\n";
    
    echo "\nAll tests passed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}