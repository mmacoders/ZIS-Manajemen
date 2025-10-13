<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donatur;
use App\Models\ZisTransaction;
use App\Models\Mustahiq;
use App\Models\Distribution;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SeedMLDataCommand extends Command
{
    protected $signature = 'ml:seed-data';
    protected $description = 'Seed data for ML Analytics testing';

    public function handle()
    {
        $this->info('Seeding ML Analytics test data...');

        // Create a program if none exists
        if (Program::count() == 0) {
            $user = User::first();
            Program::create([
                'nama' => 'Program Zakat',
                'deskripsi' => 'Program distribusi zakat',
                'target_dana' => 100000000,
                'dana_terkumpul' => 0,
                'tanggal_mulai' => Carbon::now()->subYear(),
                'tanggal_selesai' => Carbon::now()->addYear(),
                'status' => 'aktif',
                'created_by' => $user->id
            ]);
            $this->info('Created sample program');
        }

        // Get the first program and user
        $program = Program::first();
        $user = User::first();

        // Create sample donaturs (donors)
        if (Donatur::count() < 50) {
            $donaturs = Donatur::factory()->count(50)->create();
            $this->info('Created 50 sample donaturs');
        } else {
            $donaturs = Donatur::take(50)->get();
            $this->info('Using existing donaturs');
        }

        // Create sample transactions for the past 12 months
        $transactionCount = ZisTransaction::count();
        if ($transactionCount < 500) {
            foreach ($donaturs as $donatur) {
                // Create 5-15 transactions per donatur
                $transactionCount = rand(5, 15);
                
                for ($i = 0; $i < $transactionCount; $i++) {
                    // Random date within the last 12 months
                    $randomDate = Carbon::now()->subMonths(rand(0, 11))->subDays(rand(0, 30));
                    
                    // Random amount between 50,000 and 2,000,000
                    $amount = rand(50000, 2000000);
                    
                    ZisTransaction::create([
                        'nomor_transaksi' => 'TRX-' . Str::random(8),
                        'donatur_id' => $donatur->id,
                        'jumlah' => $amount,
                        'jenis_zis' => ['zakat', 'infaq', 'sedekah'][rand(0, 2)],
                        'tanggal_transaksi' => $randomDate,
                        'keterangan' => 'Sample transaction',
                        'status' => 'verified'
                    ]);
                }
            }
            $this->info('Created sample transactions');
        } else {
            $this->info('Using existing transactions');
        }

        // Create sample mustahiqs (beneficiaries)
        if (Mustahiq::count() < 30) {
            $mustahiqs = Mustahiq::factory()->count(30)->create();
            $this->info('Created 30 sample mustahiqs');
        } else {
            $mustahiqs = Mustahiq::take(30)->get();
            $this->info('Using existing mustahiqs');
        }

        // Create sample distributions
        $distributionCount = Distribution::count();
        if ($distributionCount < 200) {
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
                        'program_id' => $program->id,
                        'tanggal_distribusi' => $randomDate,
                        'keterangan' => 'Sample distribution',
                        'status' => 'completed',
                        'distributed_by' => $user->id
                    ]);
                }
            }
            $this->info('Created sample distributions');
        } else {
            $this->info('Using existing distributions');
        }

        $this->info('ML Analytics test data seeding completed successfully!');
        $this->info('Total donaturs: ' . Donatur::count());
        $this->info('Total transactions: ' . ZisTransaction::count());
        $this->info('Total mustahiqs: ' . Mustahiq::count());
        $this->info('Total distributions: ' . Distribution::count());
    }
}