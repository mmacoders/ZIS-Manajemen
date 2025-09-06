<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MLAnalyticsService;

class TestMLServiceCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ml-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the ML Analytics Service';

    /**
     * Execute the console command.
     */
    public function handle(MLAnalyticsService $mlService)
    {
        try {
            $this->info('Testing ML Analytics Service...');
            
            // Test the generateAnalyticsDashboard method (only public method)
            $this->info('Generating analytics dashboard...');
            $dashboardData = $mlService->generateAnalyticsDashboard();
            
            $this->info('Dashboard data generated successfully!');
            $this->info('Keys in dashboard data: ' . implode(', ', array_keys($dashboardData)));
            
            // Display some information about the dashboard data
            $this->info("\nDashboard data summary:");
            foreach ($dashboardData as $key => $value) {
                if (is_array($value)) {
                    $this->info("- {$key}: " . count($value) . " items");
                } else {
                    $this->info("- {$key}: " . (string)$value);
                }
            }
            
            $this->info("\nAll tests passed successfully!");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }
}