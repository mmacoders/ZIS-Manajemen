<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MLAnalyticsService;

class FraudDetectionDemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fraud:detection-demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demonstrate how fraud detection works with sample data';

    /**
     * Execute the console command.
     */
    public function handle(MLAnalyticsService $mlService)
    {
        $this->info('=== FRAUD DETECTION DEMO ===');
        $this->line('');

        $this->info('Running fraud detection analysis...');
        $this->line('');

        // Run fraud detection
        $fraudResults = $mlService->detectFraudPatterns();

        $this->info('=== FRAUD DETECTION RESULTS ===');
        $this->line('');

        // Display unusual transactions
        $this->info('1. UNUSUAL TRANSACTIONS:');
        if (!empty($fraudResults['unusual_transactions'])) {
            foreach ($fraudResults['unusual_transactions'] as $transaction) {
                if (strpos($transaction['keterangan'] ?? '', 'FRAUD DEMO') !== false) {
                    $this->line("   - Transaction ID: {$transaction['id']}");
                    $this->line("   - Amount: Rp " . number_format($transaction['jumlah'], 0, ',', '.'));
                    $this->line("   - Donor: " . ($transaction['muzakki']['nama'] ?? 'Unknown'));
                    $this->line("   - Description: {$transaction['keterangan']}");
                    $this->line("   - Anomaly Score: " . round($transaction['anomaly_score'], 2) . "x above average");
                    $this->line('');
                }
            }
        } else {
            $this->line("   No unusual transactions detected.");
            $this->line('');
        }

        // Display suspicious beneficiaries
        $this->info('2. SUSPICIOUS BENEFICIARIES:');
        if (!empty($fraudResults['suspicious_beneficiaries'])) {
            foreach ($fraudResults['suspicious_beneficiaries'] as $beneficiary) {
                if (strpos($beneficiary['keterangan'] ?? '', 'FRAUD DEMO') !== false) {
                    $this->line("   - Beneficiary: {$beneficiary['nama']}");
                    $this->line("   - NIK: {$beneficiary['nik']}");
                    $this->line("   - Category: {$beneficiary['kategori']}");
                    $this->line("   - Distributions in last 6 months: {$beneficiary['distributions_count']}");
                    $this->line("   - Description: {$beneficiary['keterangan']}");
                    $this->line('');
                }
            }
        } else {
            $this->line("   No suspicious beneficiaries detected.");
            $this->line('');
        }

        // Display timing anomalies
        $this->info('3. TIMING ANOMALIES:');
        if (!empty($fraudResults['timing_anomalies'])) {
            foreach ($fraudResults['timing_anomalies'] as $transaction) {
                if (strpos($transaction['keterangan'] ?? '', 'FRAUD DEMO') !== false) {
                    $createdTime = date('H:i:s', strtotime($transaction['created_at']));
                    $transactionDate = date('Y-m-d', strtotime($transaction['tanggal_transaksi']));
                    
                    $this->line("   - Transaction ID: {$transaction['id']}");
                    $this->line("   - Amount: Rp " . number_format($transaction['jumlah'], 0, ',', '.'));
                    $this->line("   - Donor: " . ($transaction['muzakki']['nama'] ?? 'Unknown'));
                    $this->line("   - Created at: {$createdTime} (unusual time)");
                    $this->line("   - Transaction date: {$transactionDate}");
                    $this->line("   - Description: {$transaction['keterangan']}");
                    $this->line('');
                }
            }
        } else {
            $this->line("   No timing anomalies detected.");
            $this->line('');
        }

        $this->info('=== FRAUD DETECTION EXPLANATION ===');
        $this->line('');

        $this->info('The machine learning system detects fraud through three main patterns:');
        $this->line('');

        $this->info('1. UNUSUAL TRANSACTIONS:');
        $this->line('   - Detects transactions with amounts significantly higher than average');
        $this->line('   - Flags transactions that are 10x or more above the average donation');
        $this->line('   - Assigns an anomaly score based on how much the transaction exceeds normal amounts');
        $this->line('');

        $this->info('2. SUSPICIOUS BENEFICIARIES:');
        $this->line('   - Identifies beneficiaries receiving unusually frequent distributions');
        $this->line('   - Flags recipients with more than 5 distributions in a 6-month period');
        $this->line('   - Helps detect potential manipulation of the distribution system');
        $this->line('');

        $this->info('3. TIMING ANOMALIES:');
        $this->line('   - Detects transactions occurring outside normal business hours');
        $this->line('   - Flags transactions created before 6 AM or after 10 PM');
        $this->line('   - Helps identify potentially automated or suspicious activity');
        $this->line('');

        $this->info('These patterns help the ZIS system maintain integrity by automatically identifying');
        $this->info('suspicious activities that require further investigation by administrators.');
    }
}