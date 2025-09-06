<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\MuzakkiController;
use App\Http\Controllers\Api\UpzController;
use App\Http\Controllers\Api\ZisTransactionController;
use App\Http\Controllers\Api\MustahiqController;
use App\Http\Controllers\Api\ProgramController;
use App\Http\Controllers\Api\DistributionController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\OCRController;
use App\Http\Controllers\Api\ShariaAccountingController;
use App\Http\Controllers\Api\BaznasReportController;
use App\Http\Controllers\Api\MLAnalyticsController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// ML Analytics API Routes - Make public for testing
Route::prefix('ml-analytics')->group(function () {
    Route::get('dashboard', [MLAnalyticsController::class, 'dashboard']);
    Route::get('donation-predictions', [MLAnalyticsController::class, 'donationPredictions']);
    Route::get('donor-analysis', [MLAnalyticsController::class, 'donorAnalysis']);
    Route::get('beneficiary-predictions', [MLAnalyticsController::class, 'beneficiaryPredictions']);
    Route::get('fraud-detection', [MLAnalyticsController::class, 'fraudDetection']);
    Route::post('trigger-fraud-detection', [MLAnalyticsController::class, 'triggerFraudDetection']); // AI Analysis endpoint
    Route::get('performance-metrics', [MLAnalyticsController::class, 'performanceMetrics']);
    Route::get('prediction-history', [MLAnalyticsController::class, 'predictionHistory']);
    Route::get('model-statistics', [MLAnalyticsController::class, 'modelStatistics']);
    Route::post('predictions/{id}/verify', [MLAnalyticsController::class, 'verifyPrediction']);
    Route::delete('cache', [MLAnalyticsController::class, 'clearCache']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index']);
    
    // Bidang 1 - Pengumpulan routes
    Route::middleware('check.role:admin,bidang1')->group(function () {
        Route::apiResource('muzakki', MuzakkiController::class);
        Route::get('muzakki-search', [MuzakkiController::class, 'search']);
        Route::apiResource('upz', UpzController::class);
        Route::apiResource('zis-transactions', ZisTransactionController::class);
        Route::post('zis-transactions/{id}/verify', [ZisTransactionController::class, 'verify']);
        Route::get('reports/zis-transactions', [ZisTransactionController::class, 'report']);
    });
    
    // Bidang 2 - Distribusi & Pemberdayaan routes
    Route::middleware('check.role:admin,bidang2')->group(function () {
        Route::apiResource('mustahiq', MustahiqController::class);
        Route::apiResource('programs', ProgramController::class);
        Route::apiResource('distributions', DistributionController::class);
        Route::get('reports/distributions', [DistributionController::class, 'report']);
    });
    
    // Bidang 4 - Arsip Surat routes
    Route::middleware('check.role:admin,bidang4')->group(function () {
        Route::apiResource('documents', DocumentController::class);
        Route::get('documents-search', [DocumentController::class, 'search']);
        Route::get('documents/{id}/download', [DocumentController::class, 'download']);
    });
    
    // Reports - Available for all authenticated users
    Route::get('reports/summary', [ReportController::class, 'summary']);
    Route::get('reports/export-csv', [ReportController::class, 'exportCSV']);
    Route::get('reports/export-pdf', [ReportController::class, 'exportPDF']);
    Route::get('reports/export-summary-pdf', [ReportController::class, 'exportSummaryPDF']);
    
    // OCR API Routes - Available for all authenticated users
    Route::prefix('ocr')->group(function () {
        Route::post('process', [OCRController::class, 'processDocument']);
        Route::post('batch', [OCRController::class, 'processBatch']);
        Route::get('templates', [OCRController::class, 'getTemplates']);
        Route::get('statistics', [OCRController::class, 'getStatistics']);
        Route::post('validate', [OCRController::class, 'validateDocument']);
    });
    
    // Sharia Accounting API Routes - Admin and Bidang 1 access
    Route::middleware('check.role:admin,bidang1')->prefix('sharia-accounting')->group(function () {
        Route::get('dashboard', [ShariaAccountingController::class, 'dashboard']);
        Route::get('fund-categories', [ShariaAccountingController::class, 'getFundCategories']);
        Route::get('chart-of-accounts', [ShariaAccountingController::class, 'getChartOfAccounts']);
        Route::get('transactions', [ShariaAccountingController::class, 'getTransactions']);
        Route::get('financial-summary', [ShariaAccountingController::class, 'getFinancialSummary']);
        Route::post('create-from-zis', [ShariaAccountingController::class, 'createFromZisTransaction']);
        Route::post('create-from-distribution', [ShariaAccountingController::class, 'createFromDistribution']);
    });
    
    // BAZNAS Reporting API Routes - Admin access only
    Route::middleware('check.role:admin')->prefix('baznas-reports')->group(function () {
        Route::get('/', [BaznasReportController::class, 'index']);
        Route::post('/', [BaznasReportController::class, 'store']);
        Route::get('compliance-dashboard', [BaznasReportController::class, 'complianceDashboard']);
        Route::get('{id}', [BaznasReportController::class, 'show']);
        Route::put('{id}', [BaznasReportController::class, 'update']);
        Route::post('{id}/approve', [BaznasReportController::class, 'approve']);
        Route::post('{id}/submit', [BaznasReportController::class, 'submit']);
        Route::post('{id}/generate-pdf', [BaznasReportController::class, 'generatePdf']);
        Route::get('{id}/download-pdf', [BaznasReportController::class, 'downloadPdf']);
    });
    
    // ML Analytics API Routes - Admin and Bidang 1 access (authenticated version)
    Route::middleware('check.role:admin,bidang1')->prefix('ml-analytics-auth')->group(function () {
        Route::get('dashboard', [MLAnalyticsController::class, 'dashboard']);
        Route::get('donation-predictions', [MLAnalyticsController::class, 'donationPredictions']);
        Route::get('donor-analysis', [MLAnalyticsController::class, 'donorAnalysis']);
        Route::get('beneficiary-predictions', [MLAnalyticsController::class, 'beneficiaryPredictions']);
        Route::get('fraud-detection', [MLAnalyticsController::class, 'fraudDetection']);
        Route::post('trigger-fraud-detection', [MLAnalyticsController::class, 'triggerFraudDetection']); // AI Analysis endpoint
        Route::get('performance-metrics', [MLAnalyticsController::class, 'performanceMetrics']);
        Route::get('prediction-history', [MLAnalyticsController::class, 'predictionHistory']);
        Route::get('model-statistics', [MLAnalyticsController::class, 'modelStatistics']);
        Route::post('predictions/{id}/verify', [MLAnalyticsController::class, 'verifyPrediction']);
        Route::delete('cache', [MLAnalyticsController::class, 'clearCache']);
    });
});