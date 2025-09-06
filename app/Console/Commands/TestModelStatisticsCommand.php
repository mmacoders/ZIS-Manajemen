<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MLAnalyticsService;

class TestModelStatisticsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:model-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the ML Analytics Service model statistics method';

    /**
     * Execute the console command.
     */
    public function handle(MLAnalyticsService $mlService)
    {
        try {
            $this->info('Testing ML Analytics Service model statistics method...');
            
            // Test the getModelStatistics method
            $this->info('Generating model statistics...');
            $stats = $mlService->getModelStatistics();
            
            $this->info('Model statistics generated successfully!');
            $this->info('Keys in statistics data: ' . implode(', ', array_keys($stats)));
            
            // Display some information about the statistics data
            $this->info("\nStatistics data summary:");
            foreach ($stats as $key => $value) {
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