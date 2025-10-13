<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donatur;
use App\Models\ZisTransaction;
use Carbon\Carbon;

class TestTransactionCommand extends Command
{
    protected $signature = 'test:transaction {--type=individual : Donor type (individual, institution, operational)}';
    protected $description = 'Create a test transaction to verify receipt number generation';

    public function handle()
    {
        $this->info('Creating test transaction...');

        // Get donor based on type
        $type = $this->option('type');
        $donatur = null;
        
        switch ($type) {
            case 'institution':
                $donatur = Donatur::where('jenis_donatur', 'lembaga')->first();
                break;
            case 'operational':
                // For operational, we'll create a special transaction without a donor
                $donatur = null;
                break;
            case 'individual':
            default:
                $donatur = Donatur::where('jenis_donatur', 'individu')->first();
                break;
        }
        
        if ($type !== 'operational' && !$donatur) {
            $this->error("No donors found for type: {$type}");
            return 1;
        }

        if ($type !== 'operational') {
            $this->info("Using donor: {$donatur->nama} (Type: {$donatur->jenis_donatur})");
        } else {
            $this->info("Creating operational transaction (no donor)");
        }

        // Create a test transaction
        $tanggal = Carbon::now();
        $day = str_pad($tanggal->day, 2, '0', STR_PAD_LEFT);
        $month = str_pad($tanggal->month, 2, '0', STR_PAD_LEFT);
        $year = substr($tanggal->year, -1); // Last digit of year
        
        // Get donor type code
        // 01 = perorangan (individual/munfiq)
        // 02 = lembaga (institution)
        // 03 = operasional (operational)
        $donaturCode = '01'; // Default for individual/munfiq
        if ($type === 'institution') {
            $donaturCode = '02';
        } else if ($type === 'operational') {
            $donaturCode = '03';
        }
        
        // Get transaction count for the day
        $dailyCount = ZisTransaction::whereDate('tanggal_transaksi', $tanggal->toDateString())->count() + 1;
        $dailyCountFormatted = str_pad($dailyCount, 7, '0', STR_PAD_LEFT);
        
        // New receipt number format: DD/MM/Y/DC (Day/Month/YearLastDigit/DonatorCode)/(DailyCount)
        $nomor_kwitansi = "{$day}/{$month}/{$year}/{$donaturCode}/{$dailyCountFormatted}";
        
        $transactionData = [
            'nomor_transaksi' => $nomor_kwitansi,
            'jenis_zis' => 'infaq',
            'jumlah' => 250000,
            'tanggal_transaksi' => $tanggal->toDateString(),
            'keterangan' => 'Test transaction for ' . $type
        ];
        
        // Add donor ID if not operational
        if ($type !== 'operational' && $donatur) {
            $transactionData['donatur_id'] = $donatur->id;
        }

        $this->info('Transaction data:');
        $this->info(print_r($transactionData, true));

        try {
            // Create the transaction directly
            $transaction = ZisTransaction::create($transactionData);
            
            $this->info("Transaction created successfully!");
            $this->info("Receipt Number: {$transaction->nomor_transaksi}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("Error creating transaction: " . $e->getMessage());
            return 1;
        }
    }
}