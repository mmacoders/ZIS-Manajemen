<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MLAnalyticsService;
use App\Models\MLAnalyticsCache;
use App\Models\MLAnalyticsPrediction;
use App\Models\MLLearningData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MLAnalyticsController extends Controller
{
    protected $mlService;

    public function __construct(MLAnalyticsService $mlService)
    {
        $this->mlService = $mlService;
    }

    /**
     * Get ML analytics dashboard data
     */
    public function dashboard(): JsonResponse
    {
        try {
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = 'ml_dashboard_' . $userId . '_' . now()->format('Y-m-d-H');
            
            $dashboardData = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'ml_dashboard',
                function() {
                    return $this->mlService->generateAnalyticsDashboard();
                },
                60 // Cache for 1 hour
            );

            return response()->json([
                'success' => true,
                'data' => $dashboardData,
                'generated_at' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating ML analytics dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donation predictions
     */
    public function donationPredictions(Request $request): JsonResponse
    {
        try {
            $months = $request->input('months', 3); // Default predict next 3 months
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = "donation_predictions_{$months}_" . $userId . '_' . now()->format('Y-m-d');
            
            $predictions = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'donation_predictions',
                function() use ($months) {
                    $results = [];
                    for ($i = 1; $i <= $months; $i++) {
                        $prediction = $this->mlService->predictNextMonthDonations();
                        $prediction['month'] = now()->addMonths($i)->format('Y-m');
                        $results[] = $prediction;
                    }
                    return $results;
                },
                120 // Cache for 2 hours
            );

            // Store predictions in database for tracking (only if user is authenticated)
            if (auth()->check()) {
                foreach ($predictions as $prediction) {
                    MLAnalyticsPrediction::updateOrCreate([
                        'prediction_type' => 'donation',
                        'prediction_date' => Carbon::createFromFormat('Y-m', $prediction['month'])->startOfMonth()
                    ], [
                        'model_type' => 'linear_regression',
                        'prediction_result' => $prediction,
                        'confidence_score' => $prediction['confidence'],
                        'created_by' => auth()->id()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $predictions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating donation predictions',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donor behavior analysis
     */
    public function donorAnalysis(): JsonResponse
    {
        try {
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = 'donor_analysis_' . $userId . '_' . now()->format('Y-m-d');
            
            $analysis = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'donor_analysis',
                function() {
                    return $this->mlService->analyzeDonorPatterns();
                },
                180 // Cache for 3 hours
            );

            return response()->json([
                'success' => true,
                'data' => $analysis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error analyzing donor patterns',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get beneficiary need predictions
     */
    public function beneficiaryPredictions(): JsonResponse
    {
        try {
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = 'beneficiary_predictions_' . $userId . '_' . now()->format('Y-m-d');
            
            $predictions = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'beneficiary_predictions',
                function() {
                    return $this->mlService->predictBeneficiaryNeeds();
                },
                120 // Cache for 2 hours
            );

            // Store high-priority predictions (only if user is authenticated)
            if (auth()->check()) {
                foreach ($predictions['individual_predictions'] as $prediction) {
                    if ($prediction['need_probability'] > 70) {
                        MLAnalyticsPrediction::updateOrCreate([
                            'prediction_type' => 'beneficiary_need',
                            'target_type' => 'App\\Models\\Mustahiq',
                            'target_id' => $prediction['beneficiary']['id'],
                            'prediction_date' => now()->toDateString()
                        ], [
                            'model_type' => 'pattern_analysis',
                            'prediction_result' => $prediction,
                            'confidence_score' => $prediction['need_probability'],
                            'created_by' => auth()->id()
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $predictions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error predicting beneficiary needs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fraud detection analysis
     */
    public function fraudDetection(): JsonResponse
    {
        try {
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = 'fraud_detection_' . $userId . '_' . now()->format('Y-m-d-H');
            
            $fraudAnalysis = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'fraud_detection',
                function() {
                    return $this->mlService->detectFraudPatterns();
                },
                30 // Cache for 30 minutes - fraud detection should be more frequent
            );

            return response()->json([
                'success' => true,
                'data' => $fraudAnalysis
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error detecting fraud patterns',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger immediate fraud detection analysis (AI Analysis)
     */
    public function triggerFraudDetection(): JsonResponse
    {
        try {
            // Immediately run fraud detection without using cache
            $fraudAnalysis = $this->mlService->detectFraudPatterns();
            
            // Clear the fraud detection cache to ensure fresh results on next regular call
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKeyPattern = 'fraud_detection_' . $userId . '_' . now()->format('Y-m-d');
            MLAnalyticsCache::where('cache_key', 'like', $cacheKeyPattern . '%')->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fraud detection analysis completed successfully',
                'data' => $fraudAnalysis,
                'analysis_time' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error running fraud detection analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get performance metrics
     */
    public function performanceMetrics(): JsonResponse
    {
        try {
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = 'performance_metrics_' . $userId . '_' . now()->format('Y-m-d');
            
            $metrics = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'performance_metrics',
                function() {
                    return $this->mlService->calculatePerformanceMetrics();
                },
                120 // Cache for 2 hours
            );

            return response()->json([
                'success' => true,
                'data' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating performance metrics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get prediction history
     */
    public function predictionHistory(): JsonResponse
    {
        try {
            // Get prediction history (only for authenticated users)
            if (!auth()->check()) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $history = MLAnalyticsPrediction::where('created_by', auth()->id())
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving prediction history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get model statistics
     */
    public function modelStatistics(): JsonResponse
    {
        try {
            $userId = auth()->check() ? auth()->id() : 0;
            $cacheKey = 'model_statistics_' . $userId . '_' . now()->format('Y-m-d');
            
            $stats = MLAnalyticsCache::getOrCompute(
                $cacheKey,
                'model_statistics',
                function() {
                    return $this->mlService->getModelStatistics();
                },
                180 // Cache for 3 hours
            );

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving model statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify a prediction
     */
    public function verifyPrediction(Request $request, $id): JsonResponse
    {
        try {
            // Only authenticated users can verify predictions
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'actual_value' => 'required|numeric',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $prediction = MLAnalyticsPrediction::findOrFail($id);
            
            if ($prediction->created_by !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $prediction->update([
                'actual_value' => $request->input('actual_value'),
                'verified_at' => now(),
                'verification_notes' => $request->input('notes'),
                'verified_by' => auth()->id()
            ]);

            // Store learning data for model improvement
            MLLearningData::create([
                'prediction_id' => $prediction->id,
                'model_type' => $prediction->model_type,
                'predicted_value' => $prediction->prediction_result['predicted_amount'] ?? 0,
                'actual_value' => $request->input('actual_value'),
                'error_margin' => abs(($request->input('actual_value') - ($prediction->prediction_result['predicted_amount'] ?? 0)) / $request->input('actual_value') * 100),
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Prediction verified successfully',
                'data' => $prediction
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error verifying prediction',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear ML analytics cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            // Only authenticated users can clear cache
            if (!auth()->check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentication required'
                ], 401);
            }

            MLAnalyticsCache::truncate(); // Clear all cache entries

            return response()->json([
                'success' => true,
                'message' => 'ML analytics cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing ML analytics cache',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}