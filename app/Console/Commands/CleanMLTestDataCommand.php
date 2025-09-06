<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Muzakki;
use App\Models\ZisTransaction;
use App\Models\Mustahiq;
use App\Models\Distribution;
use App\Models\Program;

class CleanMLTestDataCommand extends Command
{
    protected $signature = 'ml:clean-test-data';
    protected $description = 'Clean up test data created for ML Analytics testing';

    public function handle()
    {
        $this->info('Cleaning up ML Analytics test data...');
        
        // Ask for confirmation before deleting data
        if (!$this->confirm('This will delete all sample data created for testing ML Analytics. Do you wish to continue?')) {
            $this->info('Operation cancelled.');
            return;
        }
        
        try {
            // Delete test data
            $this->info('Deleting ZIS transactions...');
            ZisTransaction::query()->delete();
            
            $this->info('Deleting distributions...');
            Distribution::query()->delete();
            
            $this->info('Deleting muzakkis...');
            Muzakki::query()->delete();
            
            $this->info('Deleting mustahiqs...');
            Mustahiq::query()->delete();
            
            // Note: We're not deleting programs as they might be used by other parts of the system
            $this->info('Test data cleanup completed successfully!');
            
            // Show final counts
            $this->info('Final counts:');
            $this->info('Muzakki: ' . Muzakki::count());
            $this->info('ZIS Transactions: ' . ZisTransaction::count());
            $this->info('Mustahiq: ' . Mustahiq::count());
            $this->info('Distributions: ' . Distribution::count());
            
        } catch (\Exception $e) {
            $this->error('Error cleaning up test data: ' . $e->getMessage());
        }
    }
}