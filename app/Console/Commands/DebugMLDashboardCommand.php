<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MLAnalyticsService;
use App\Models\MLAnalyticsCache;

class DebugMLDashboardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:ml-dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug the ML Analytics Dashboard generation';

    /**
     * Execute the console command.
     */
    public function handle(MLAnalyticsService $mlService)
    {
        try {
            $this->info('Debugging ML Analytics Dashboard generation...');
            
            // Test cache key generation
            $userId = 0; // No user for console command
            $cacheKey = 'ml_dashboard_' . $userId . '_' . now()->format('Y-m-d-H');
            $this->info("Cache key: {$cacheKey}");
            
            // Test the cache system
            $this->info('Testing cache system...');
            $cache = MLAnalyticsCache::valid()->where('cache_key', $cacheKey)->first();
            if ($cache) {
                $this->info('Found existing cache entry');
                $cache->incrementHit();
                $dashboardData = $cache->cache_data;
            } else {
                $this->info('No existing cache entry, computing new data...');
                
                // Test the service method directly
                $this->info('Calling generateAnalyticsDashboard method...');
                $dashboardData = $mlService->generateAnalyticsDashboard();
                
                $this->info('Dashboard data keys: ' . implode(', ', array_keys($dashboardData)));
                
                // Try to store in cache
                $this->info('Storing data in cache...');
                MLAnalyticsCache::create([
                    'cache_key' => $cacheKey,
                    'analytics_type' => 'ml_dashboard',
                    'cache_data' => $dashboardData,
                    'computed_at' => now(),
                    'expires_at' => now()->addMinutes(60),
                    'is_valid' => true,
                    'hit_count' => 1,
                    'last_accessed_at' => now()
                ]);
                
                $this->info('Data stored in cache successfully');
            }
            
            $this->info('Dashboard data generated successfully!');
            $this->info(json_encode($dashboardData, JSON_PRETTY_PRINT));
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile());
            $this->error("Line: " . $e->getLine());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}