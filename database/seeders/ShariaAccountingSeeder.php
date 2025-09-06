<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ShariaFundCategory;
use App\Models\ShariaAccount;
use App\Models\ShariaTransaction;

class ShariaAccountingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Fund Categories with BAZNAS compliance
        $this->createFundCategories();
        
        // Create Chart of Accounts
        $this->createChartOfAccounts();
        
        // Create sample transactions
        $this->createSampleTransactions();
    }
    
    private function createFundCategories(): void
    {
        $categories = [
            [
                'code' => 'ZAK',
                'name' => 'Dana Zakat',
                'name_ar' => 'صندوق الزكاة',
                'description' => 'Dana zakat yang terkumpul dari muzakki',
                'type' => 'zakat',
                'amil_percentage' => 12.5, // BAZNAS standard
                'distribution_rules' => [
                    'fakir' => 25,
                    'miskin' => 25,
                    'amil' => 12.5,
                    'muallaf' => 5,
                    'riqab' => 5,
                    'gharim' => 10,
                    'fisabilillah' => 12.5,
                    'ibnu_sabil' => 5
                ],
                'is_active' => true,
                'is_baznas_compliant' => true
            ],
            [
                'code' => 'INF',
                'name' => 'Dana Infaq',
                'name_ar' => 'صندوق الإنفاق',
                'description' => 'Dana infaq dari donatur',
                'type' => 'infaq',
                'amil_percentage' => 10.0,
                'distribution_rules' => [
                    'program_pendidikan' => 40,
                    'program_kesehatan' => 30,
                    'program_ekonomi' => 20,
                    'amil' => 10
                ],
                'is_active' => true,
                'is_baznas_compliant' => true
            ],
            [
                'code' => 'SED',
                'name' => 'Dana Sedekah',
                'name_ar' => 'صندوق الصدقة',
                'description' => 'Dana sedekah untuk keperluan darurat',
                'type' => 'sedekah',
                'amil_percentage' => 8.0,
                'distribution_rules' => [
                    'bantuan_darurat' => 60,
                    'program_sosial' => 32,
                    'amil' => 8
                ],
                'is_active' => true,
                'is_baznas_compliant' => true
            ],
            [
                'code' => 'AMI',
                'name' => 'Dana Amil',
                'name_ar' => 'صندوق العاملين',
                'description' => 'Dana untuk operasional amil zakat',
                'type' => 'amil',
                'amil_percentage' => 0.0, // Amil fund itself
                'distribution_rules' => [
                    'gaji_karyawan' => 60,
                    'operasional_kantor' => 25,
                    'pengembangan_sdm' => 15
                ],
                'is_active' => true,
                'is_baznas_compliant' => true
            ],
            [
                'code' => 'OPR',
                'name' => 'Dana Operasional',
                'name_ar' => 'الصندوق التشغيلي',
                'description' => 'Dana untuk operasional organisasi',
                'type' => 'operational',
                'amil_percentage' => 0.0,
                'distribution_rules' => [
                    'administrasi' => 40,
                    'teknologi' => 30,
                    'pemasaran' => 20,
                    'cadangan' => 10
                ],
                'is_active' => true,
                'is_baznas_compliant' => true
            ]
        ];
        
        foreach ($categories as $category) {
            ShariaFundCategory::create($category);
        }
    }
    
    private function createChartOfAccounts(): void
    {
        $zakat = ShariaFundCategory::where('code', 'ZAK')->first();
        $infaq = ShariaFundCategory::where('code', 'INF')->first();
        $sedekah = ShariaFundCategory::where('code', 'SED')->first();
        $amil = ShariaFundCategory::where('code', 'AMI')->first();
        $operational = ShariaFundCategory::where('code', 'OPR')->first();
        
        $accounts = [
            // ASSETS
            [
                'account_code' => '1000',
                'account_name' => 'Kas dan Bank',
                'account_name_ar' => 'النقد والبنك',
                'fund_category_id' => $operational->id,
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'level' => 1,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'assets', 'line' => 'cash_and_bank']
            ],
            [
                'account_code' => '1100',
                'account_name' => 'Kas Zakat',
                'fund_category_id' => $zakat->id,
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'assets', 'line' => 'zakat_cash']
            ],
            [
                'account_code' => '1200',
                'account_name' => 'Kas Infaq',
                'fund_category_id' => $infaq->id,
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'assets', 'line' => 'infaq_cash']
            ],
            [
                'account_code' => '1300',
                'account_name' => 'Kas Sedekah',
                'fund_category_id' => $sedekah->id,
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'assets', 'line' => 'sedekah_cash']
            ],
            [
                'account_code' => '1400',
                'account_name' => 'Kas Amil',
                'fund_category_id' => $amil->id,
                'account_type' => 'asset',
                'normal_balance' => 'debit',
                'parent_code' => '1000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'assets', 'line' => 'amil_cash']
            ],
            
            // LIABILITIES
            [
                'account_code' => '2000',
                'account_name' => 'Dana Titipan',
                'account_name_ar' => 'الأموال المؤتمنة',
                'fund_category_id' => $operational->id,
                'account_type' => 'liability',
                'normal_balance' => 'credit',
                'level' => 1,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'liabilities', 'line' => 'trust_funds']
            ],
            [
                'account_code' => '2100',
                'account_name' => 'Dana Zakat Belum Disalurkan',
                'fund_category_id' => $zakat->id,
                'account_type' => 'liability',
                'normal_balance' => 'credit',
                'parent_code' => '2000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'liabilities', 'line' => 'undistributed_zakat']
            ],
            [
                'account_code' => '2200',
                'account_name' => 'Dana Infaq Belum Disalurkan',
                'fund_category_id' => $infaq->id,
                'account_type' => 'liability',
                'normal_balance' => 'credit',
                'parent_code' => '2000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'liabilities', 'line' => 'undistributed_infaq']
            ],
            
            // EQUITY
            [
                'account_code' => '3000',
                'account_name' => 'Saldo Dana',
                'account_name_ar' => 'رصيد الصندوق',
                'fund_category_id' => $operational->id,
                'account_type' => 'equity',
                'normal_balance' => 'credit',
                'level' => 1,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'equity', 'line' => 'fund_balance']
            ],
            
            // REVENUE
            [
                'account_code' => '4000',
                'account_name' => 'Penerimaan ZIS',
                'account_name_ar' => 'إيرادات الزكاة والإنفاق والصدقة',
                'fund_category_id' => $operational->id,
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'level' => 1,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'revenue', 'line' => 'zis_collection']
            ],
            [
                'account_code' => '4100',
                'account_name' => 'Penerimaan Zakat',
                'fund_category_id' => $zakat->id,
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'revenue', 'line' => 'zakat_collection']
            ],
            [
                'account_code' => '4200',
                'account_name' => 'Penerimaan Infaq',
                'fund_category_id' => $infaq->id,
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'revenue', 'line' => 'infaq_collection']
            ],
            [
                'account_code' => '4300',
                'account_name' => 'Penerimaan Sedekah',
                'fund_category_id' => $sedekah->id,
                'account_type' => 'revenue',
                'normal_balance' => 'credit',
                'parent_code' => '4000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'revenue', 'line' => 'sedekah_collection']
            ],
            
            // EXPENSES
            [
                'account_code' => '5000',
                'account_name' => 'Penyaluran ZIS',
                'account_name_ar' => 'توزيع الزكاة والإنفاق والصدقة',
                'fund_category_id' => $operational->id,
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'level' => 1,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'expenses', 'line' => 'zis_distribution']
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Penyaluran Zakat',
                'fund_category_id' => $zakat->id,
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'parent_code' => '5000',
                'level' => 2,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'expenses', 'line' => 'zakat_distribution']
            ],
            [
                'account_code' => '6000',
                'account_name' => 'Biaya Operasional Amil',
                'account_name_ar' => 'مصاريف تشغيل العاملين',
                'fund_category_id' => $amil->id,
                'account_type' => 'expense',
                'normal_balance' => 'debit',
                'level' => 1,
                'is_baznas_required' => true,
                'baznas_mapping' => ['section' => 'expenses', 'line' => 'amil_operational']
            ]
        ];
        
        foreach ($accounts as $account) {
            ShariaAccount::create($account);
        }
    }
    
    private function createSampleTransactions(): void
    {
        // This would normally be triggered by actual ZIS transactions
        // For demo purposes, we'll create a few sample transactions
        
        $zakatCategory = ShariaFundCategory::where('code', 'ZAK')->first();
        $zakatCash = ShariaAccount::where('account_code', '1100')->first();
        $zakatRevenue = ShariaAccount::where('account_code', '4100')->first();
        $zakatLiability = ShariaAccount::where('account_code', '2100')->first();
        
        // Sample zakat collection transaction
        ShariaTransaction::create([
            'transaction_number' => ShariaTransaction::generateTransactionNumber(),
            'transaction_date' => now()->subDays(10),
            'transaction_type' => 'collection',
            'fund_category_id' => $zakatCategory->id,
            'debit_account_id' => $zakatCash->id,
            'credit_account_id' => $zakatLiability->id,
            'amount' => 5000000,
            'amil_amount' => 625000, // 12.5%
            'reference_type' => 'zis_transactions',
            'reference_id' => 1,
            'description' => 'Penerimaan zakat dari Muzakki - Demo',
            'baznas_notes' => 'Transaksi sesuai standar BAZNAS',
            'is_baznas_compliant' => true,
            'status' => 'posted',
            'created_by' => 1
        ]);
        
        // Update account balances
        $zakatCash->increment('current_balance', 5000000);
        $zakatLiability->increment('current_balance', 5000000);
    }
}
