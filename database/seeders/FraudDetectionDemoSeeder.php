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

class FraudDetectionDemoSeeder extends Seeder
{
    /**
     * Run the database seeds to demonstrate fraud detection.
     */
    public function run(): void
    {
        // Clear any existing fraud demo data
        $this->clearFraudDemoData();
        
        // Create normal distribution of transactions
        $this->createNormalTransactions();
        
        // Create unusual transactions (extremely high amounts) - FRAUD TYPE 1
        $this->createUnusualTransactions();
        
        // Create suspicious beneficiaries (too many distributions) - FRAUD TYPE 2
        $this->createSuspiciousBeneficiaries();
        
        // Create timing anomalies (transactions at odd hours) - FRAUD TYPE 3
        $this->createTimingAnomalies();
        
        $this->command->info('Fraud detection demo data created successfully!');
        $this->command->info('Now run the fraud detection to see these patterns identified.');
    }
    
    private function clearFraudDemoData(): void
    {
        // Clear fraud demo data (identified by special demo identifiers)
        ZisTransaction::where('keterangan', 'like', '%FRAUD DEMO%')->delete();
        Distribution::where('keterangan', 'like', '%FRAUD DEMO%')->delete();
        Muzakki::where('keterangan', 'like', '%FRAUD DEMO%')->delete();
        Mustahiq::where('keterangan', 'like', '%FRAUD DEMO%')->delete();
    }
    
    private function createNormalTransactions(): void
    {
        $this->command->info('Creating normal transactions...');
        
        // Create normal donors
        for ($i = 1; $i <= 20; $i++) {
            $donor = Muzakki::create([
                'nama' => 'Donor ' . $i,
                'nik' => 'DONOR' . str_pad($i, 12, '0', STR_PAD_LEFT),
                'jenis' => 'individu',
                'alamat' => 'Address ' . $i,
                'telepon' => '081234567' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'email' => 'donor' . $i . '@example.com',
                'keterangan' => 'NORMAL DONOR - FRAUD DEMO'
            ]);
            
            // Create 1-3 normal transactions per donor
            $transactionCount = rand(1, 3);
            for ($j = 0; $j < $transactionCount; $j++) {
                // Random amount between 50,000 and 2,000,000 (normal range)
                $amount = rand(50000, 2000000);
                
                ZisTransaction::create([
                    'nomor_transaksi' => 'TRX-NORM' . str_pad($i, 3, '0', STR_PAD_LEFT) . str_pad($j, 2, '0', STR_PAD_LEFT),
                    'donatur_id' => $donor->id,
                    'jumlah' => $amount,
                    'jenis_zis' => ['zakat', 'infaq', 'sedekah'][rand(0, 2)],
                    'tanggal_transaksi' => Carbon::now()->subDays(rand(1, 60)),
                    'keterangan' => 'Normal transaction - FRAUD DEMO',
                    'status' => 'verified'
                ]);
            }
        }
    }
    
    private function createUnusualTransactions(): void
    {
        $this->command->info('Creating unusual transactions...');
        
        // Get an existing normal donor
        $donor = Muzakki::where('keterangan', 'like', '%NORMAL DONOR%')->first();
        if (!$donor) {
            // Create a donor if none exists
            $donor = Muzakki::create([
                'nama' => 'Fraud Donor',
                'nik' => 'FRAUD999999999999',
                'jenis' => 'individu',
                'alamat' => 'Fraud Address',
                'telepon' => '089999999999',
                'email' => 'fraud@example.com',
                'keterangan' => 'FRAUD DONOR - FRAUD DEMO'
            ]);
        }
        
        // Create an extremely unusually large transaction (FRAUD!)
        ZisTransaction::create([
            'nomor_transaksi' => 'TRX-FRAUD001',
            'donatur_id' => $donor->id,
            'jumlah' => 50000000, // 50 million - extremely unusual!
            'jenis_zis' => 'zakat',
            'tanggal_transaksi' => Carbon::now()->subDays(5),
            'keterangan' => 'EXTREMELY UNUSUAL TRANSACTION - FRAUD DEMO',
            'status' => 'verified'
        ]);
    }
    
    private function createSuspiciousBeneficiaries(): void
    {
        $this->command->info('Creating suspicious beneficiaries...');
        
        // Create a beneficiary who receives too many distributions
        $beneficiary = Mustahiq::create([
            'nama' => 'Suspect Recipient',
            'nik' => 'SUSPECT1122334455',
            'kategori' => 'fakir',
            'alamat' => 'Jl. Kebon Jeruk No. 20, Jakarta',
            'telepon' => '08222222222',
            'status' => 'aktif',
            'keterangan' => 'SUSPICIOUS BENEFICIARY - FRAUD DEMO'
        ]);
        
        // Get the first user as the distributor
        $user = \App\Models\User::first();
        
        // Create many distributions for this beneficiary in a short time (FRAUD!)
        for ($i = 1; $i <= 8; $i++) { // 8 distributions in 6 months - suspicious!
            Distribution::create([
                'nomor_distribusi' => 'DIST-FD' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'mustahiq_id' => $beneficiary->id,
                'jumlah' => 1500000,
                'program_id' => 1,
                'tanggal_distribusi' => Carbon::now()->subMonths(5)->addDays($i * 7), // Every week
                'keterangan' => "Frequent distribution #{$i} - FRAUD DEMO",
                'status' => 'completed',
                'distributed_by' => $user->id
            ]);
        }
    }
    
    private function createTimingAnomalies(): void
    {
        $this->command->info('Creating timing anomalies...');
        
        // Create a donor for timing anomaly
        $donor = Muzakki::create([
            'nama' => 'Night Owl Donor',
            'nik' => 'NIGHT556677889900',
            'jenis' => 'individu',
            'alamat' => 'Jl. Senen No. 15, Jakarta',
            'telepon' => '08333333333',
            'email' => 'nightowl@example.com',
            'keterangan' => 'TIMING ANOMALY DONOR - FRAUD DEMO'
        ]);
        
        // Create a transaction at an unusual time (2:30 AM) - FRAUD!
        $transaction = ZisTransaction::create([
            'nomor_transaksi' => 'TRX-TIME001',
            'donatur_id' => $donor->id,
            'jumlah' => 2000000,
            'jenis_zis' => 'infaq',
            'tanggal_transaksi' => Carbon::now()->subDays(2),
            'keterangan' => 'TRANSACTION AT UNUSUAL TIME - FRAUD DEMO',
            'status' => 'verified'
        ]);
        
        // Manually update the created_at timestamp to simulate unusual timing
        $transaction->created_at = Carbon::now()->subDays(2)->setTime(2, 30, 0);
        $transaction->save();
    }
}