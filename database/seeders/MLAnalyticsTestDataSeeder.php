<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Muzakki;
use App\Models\ZisTransaction;
use App\Models\Mustahiq;
use App\Models\Distribution;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MLAnalyticsTestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample muzakkis (donors)
        $muzakkis = Muzakki::factory()->count(50)->create();
        
        // Create sample transactions for the past 12 months
        foreach ($muzakkis as $muzakki) {
            // Create 5-15 transactions per muzakki
            $transactionCount = rand(5, 15);
            
            for ($i = 0; $i < $transactionCount; $i++) {
                // Random date within the last 12 months
                $randomDate = Carbon::now()->subMonths(rand(0, 11))->subDays(rand(0, 30));
                
                // Random amount between 50,000 and 2,000,000
                $amount = rand(50000, 2000000);
                
                ZisTransaction::create([
                    'nomor_transaksi' => 'TRX-' . Str::random(8),
                    'muzakki_id' => $muzakki->id,
                    'jumlah' => $amount,
                    'jenis_zis' => ['zakat', 'infaq', 'sedekah'][rand(0, 2)],
                    'tanggal_transaksi' => $randomDate,
                    'keterangan' => 'Sample transaction',
                    'status' => 'verified'
                ]);
            }
        }
        
        // Create sample mustahiqs (beneficiaries)
        $mustahiqs = Mustahiq::factory()->count(30)->create();
        
        // Get the first user as the distributor
        $user = \App\Models\User::first();
        
        // Create sample distributions
        foreach ($mustahiqs as $mustahiq) {
            // Create 2-8 distributions per mustahiq
            $distributionCount = rand(2, 8);
            
            for ($i = 0; $i < $distributionCount; $i++) {
                // Random date within the last 12 months
                $randomDate = Carbon::now()->subMonths(rand(0, 11))->subDays(rand(0, 30));
                
                // Random amount between 100,000 and 1,000,000
                $amount = rand(100000, 1000000);
                
                Distribution::create([
                    'nomor_distribusi' => 'DIST-' . Str::random(8),
                    'mustahiq_id' => $mustahiq->id,
                    'jumlah' => $amount,
                    'program_id' => 1, // Assuming program ID 1 exists
                    'tanggal_distribusi' => $randomDate,
                    'keterangan' => 'Sample distribution',
                    'status' => 'completed',
                    'distributed_by' => $user->id
                ]);
            }
        }
        
        $this->command->info('ML Analytics test data seeded successfully!');
    }
}