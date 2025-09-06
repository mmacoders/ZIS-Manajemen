<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShariaFundCategory;
use App\Models\ShariaAccount;
use App\Models\ShariaTransaction;
use App\Models\ZisTransaction;
use App\Models\Distribution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ShariaAccountingController extends Controller
{
    /**
     * Get dashboard overview for Sharia Accounting
     */
    public function dashboard(): JsonResponse
    {
        try {
            $currentMonth = Carbon::now()->startOfMonth();
            $currentYear = Carbon::now()->startOfYear();
            
            // Get fund balances by category
            $fundBalances = ShariaFundCategory::active()
                ->with('accounts')
                ->get()
                ->map(function ($category) {
                    return [
                        'category' => $category->name,
                        'code' => $category->code,
                        'type' => $category->type,
                        'balance' => $category->getTotalBalance(),
                        'is_baznas_compliant' => $category->isBaznasCompliant()
                    ];
                });
            
            // Get monthly transactions summary
            $monthlyTransactions = ShariaTransaction::byDateRange($currentMonth, now())
                ->posted()
                ->selectRaw('
                    transaction_type,
                    SUM(amount) as total_amount,
                    COUNT(*) as transaction_count
                ')
                ->groupBy('transaction_type')
                ->get();
            
            // Get yearly summary
            $yearlySummary = ShariaTransaction::byDateRange($currentYear, now())
                ->posted()
                ->selectRaw('
                    SUM(CASE WHEN transaction_type = "collection" THEN amount ELSE 0 END) as total_collection,
                    SUM(CASE WHEN transaction_type = "distribution" THEN amount ELSE 0 END) as total_distribution,
                    SUM(amil_amount) as total_amil
                ')
                ->first();
            
            // Get compliance metrics
            $complianceMetrics = $this->getComplianceMetrics();
            
            // Get recent transactions
            $recentTransactions = ShariaTransaction::with([
                'fundCategory:id,name,code',
                'debitAccount:id,account_name',
                'creditAccount:id,account_name'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'fund_balances' => $fundBalances,
                    'monthly_transactions' => $monthlyTransactions,
                    'yearly_summary' => $yearlySummary,
                    'compliance_metrics' => $complianceMetrics,
                    'recent_transactions' => $recentTransactions
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get fund categories
     */
    public function getFundCategories(): JsonResponse
    {
        try {
            $categories = ShariaFundCategory::active()
                ->with('accounts')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'code' => $category->code,
                        'name' => $category->name,
                        'name_ar' => $category->name_ar,
                        'type' => $category->type,
                        'amil_percentage' => $category->amil_percentage,
                        'total_balance' => $category->getTotalBalance(),
                        'is_baznas_compliant' => $category->isBaznasCompliant(),
                        'distribution_rules' => $category->getAsnafDistribution(),
                        'accounts_count' => $category->accounts->count()
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching fund categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get chart of accounts
     */
    public function getChartOfAccounts(): JsonResponse
    {
        try {
            $accounts = ShariaAccount::active()
                ->with(['fundCategory:id,name,code', 'parent', 'children'])
                ->orderBy('account_code')
                ->get()
                ->map(function ($account) {
                    return [
                        'id' => $account->id,
                        'account_code' => $account->account_code,
                        'account_name' => $account->account_name,
                        'account_name_ar' => $account->account_name_ar,
                        'account_type' => $account->account_type,
                        'normal_balance' => $account->normal_balance,
                        'level' => $account->level,
                        'current_balance' => $account->current_balance,
                        'fund_category' => $account->fundCategory,
                        'parent_code' => $account->parent_code,
                        'is_control_account' => $account->isControlAccount(),
                        'hierarchy_path' => $account->getHierarchyPath(),
                        'is_baznas_required' => $account->is_baznas_required,
                        'baznas_mapping' => $account->baznas_mapping
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => $accounts
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching chart of accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get transactions with filters
     */
    public function getTransactions(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 15);
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $transactionType = $request->input('transaction_type');
            $fundCategoryId = $request->input('fund_category_id');
            $status = $request->input('status');
            
            $query = ShariaTransaction::with([
                'fundCategory:id,name,code',
                'debitAccount:id,account_code,account_name',
                'creditAccount:id,account_code,account_name',
                'muzakki:id,nama',
                'mustahiq:id,nama',
                'creator:id,name',
                'approver:id,name'
            ]);
            
            // Apply filters
            if ($startDate && $endDate) {
                $query->byDateRange($startDate, $endDate);
            }
            
            if ($transactionType) {
                $query->byType($transactionType);
            }
            
            if ($fundCategoryId) {
                $query->where('fund_category_id', $fundCategoryId);
            }
            
            if ($status) {
                $query->where('status', $status);
            }
            
            $transactions = $query->orderBy('transaction_date', 'desc')
                ->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching transactions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create transaction from ZIS collection
     */
    public function createFromZisTransaction(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'zis_transaction_id' => 'required|exists:zis_transactions,id',
                'fund_category_id' => 'required|exists:sharia_fund_categories,id'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $zisTransaction = ZisTransaction::findOrFail($request->zis_transaction_id);
            $fundCategory = ShariaFundCategory::findOrFail($request->fund_category_id);
            
            // Get appropriate accounts
            $cashAccount = ShariaAccount::where('fund_category_id', $fundCategory->id)
                ->where('account_type', 'asset')
                ->where('account_code', 'LIKE', '11%') // Cash accounts
                ->first();
                
            $liabilityAccount = ShariaAccount::where('fund_category_id', $fundCategory->id)
                ->where('account_type', 'liability')
                ->where('account_code', 'LIKE', '21%') // Fund liability accounts
                ->first();
            
            if (!$cashAccount || !$liabilityAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required accounts not found for this fund category'
                ], 400);
            }
            
            DB::beginTransaction();
            
            // Create main collection transaction
            $transaction = ShariaTransaction::create([
                'transaction_number' => ShariaTransaction::generateTransactionNumber(),
                'transaction_date' => $zisTransaction->tanggal_transaksi,
                'transaction_type' => 'collection',
                'fund_category_id' => $fundCategory->id,
                'debit_account_id' => $cashAccount->id,
                'credit_account_id' => $liabilityAccount->id,
                'amount' => $zisTransaction->jumlah,
                'amil_amount' => $fundCategory->calculateAmilAmount($zisTransaction->jumlah),
                'reference_type' => 'zis_transactions',
                'reference_id' => $zisTransaction->id,
                'muzakki_id' => $zisTransaction->muzakki_id,
                'description' => 'Penerimaan ' . $fundCategory->name . ' dari ' . $zisTransaction->muzakki->nama,
                'baznas_notes' => 'Transaksi sesuai standar BAZNAS',
                'is_baznas_compliant' => true,
                'status' => 'approved',
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);
            
            // Post the transaction
            $transaction->postTransaction();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sharia transaction created successfully',
                'data' => $transaction->load([
                    'fundCategory',
                    'debitAccount',
                    'creditAccount',
                    'muzakki'
                ])
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating sharia transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Create transaction from distribution
     */
    public function createFromDistribution(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'distribution_id' => 'required|exists:distributions,id',
                'mustahiq_category' => 'required|in:fakir,miskin,amil,muallaf,riqab,gharim,fisabilillah,ibnu_sabil'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $distribution = Distribution::findOrFail($request->distribution_id);
            
            // Determine fund category based on program
            $fundCategory = ShariaFundCategory::where('type', 'zakat')->first(); // Default to zakat
            
            // Get appropriate accounts
            $liabilityAccount = ShariaAccount::where('fund_category_id', $fundCategory->id)
                ->where('account_type', 'liability')
                ->first();
                
            $expenseAccount = ShariaAccount::where('fund_category_id', $fundCategory->id)
                ->where('account_type', 'expense')
                ->first();
            
            if (!$liabilityAccount || !$expenseAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Required accounts not found'
                ], 400);
            }
            
            DB::beginTransaction();
            
            $transaction = ShariaTransaction::create([
                'transaction_number' => ShariaTransaction::generateTransactionNumber(),
                'transaction_date' => $distribution->tanggal_distribusi,
                'transaction_type' => 'distribution',
                'fund_category_id' => $fundCategory->id,
                'debit_account_id' => $expenseAccount->id,
                'credit_account_id' => $liabilityAccount->id,
                'amount' => $distribution->jumlah_distribusi,
                'amil_amount' => 0, // No amil for distribution
                'reference_type' => 'distributions',
                'reference_id' => $distribution->id,
                'mustahiq_id' => $distribution->mustahiq_id,
                'mustahiq_category' => $request->mustahiq_category,
                'description' => 'Penyaluran kepada ' . $distribution->mustahiq->nama . ' - ' . $distribution->program->nama,
                'baznas_notes' => 'Distribusi untuk asnaf ' . $request->mustahiq_category,
                'is_baznas_compliant' => true,
                'status' => 'approved',
                'created_by' => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);
            
            $transaction->postTransaction();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Distribution transaction created successfully',
                'data' => $transaction->load([
                    'fundCategory',
                    'debitAccount',
                    'creditAccount',
                    'mustahiq'
                ])
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error creating distribution transaction',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get compliance metrics
     */
    private function getComplianceMetrics(): array
    {
        $currentYear = Carbon::now()->startOfYear();
        
        $totalTransactions = ShariaTransaction::byDateRange($currentYear, now())->count();
        $compliantTransactions = ShariaTransaction::byDateRange($currentYear, now())
            ->baznasCompliant()
            ->count();
            
        $complianceRate = $totalTransactions > 0 
            ? ($compliantTransactions / $totalTransactions) * 100 
            : 100;
        
        // Check amil percentage compliance
        $totalCollection = ShariaTransaction::byDateRange($currentYear, now())
            ->byType('collection')
            ->sum('amount');
            
        $totalAmil = ShariaTransaction::byDateRange($currentYear, now())
            ->sum('amil_amount');
            
        $averageAmilPercentage = $totalCollection > 0 
            ? ($totalAmil / $totalCollection) * 100 
            : 0;
        
        $amilCompliance = $averageAmilPercentage <= 12.5;
        
        return [
            'compliance_rate' => round($complianceRate, 2),
            'total_transactions' => $totalTransactions,
            'compliant_transactions' => $compliantTransactions,
            'amil_percentage' => round($averageAmilPercentage, 2),
            'amil_compliance' => $amilCompliance,
            'baznas_standard_amil' => 12.5
        ];
    }
    
    /**
     * Get financial summary for a period
     */
    public function getFinancialSummary(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
            $endDate = $request->input('end_date', Carbon::now());
            
            $summary = ShariaTransaction::byDateRange($startDate, $endDate)
                ->posted()
                ->selectRaw('
                    SUM(CASE WHEN transaction_type = "collection" THEN amount ELSE 0 END) as total_collection,
                    SUM(CASE WHEN transaction_type = "distribution" THEN amount ELSE 0 END) as total_distribution,
                    SUM(CASE WHEN transaction_type = "amil_allocation" THEN amount ELSE 0 END) as total_amil_allocation,
                    SUM(amil_amount) as total_amil,
                    COUNT(CASE WHEN transaction_type = "collection" THEN 1 END) as collection_count,
                    COUNT(CASE WHEN transaction_type = "distribution" THEN 1 END) as distribution_count
                ')
                ->first();
            
            // Get balances by fund category
            $fundBalances = ShariaFundCategory::active()
                ->get()
                ->map(function ($category) {
                    return [
                        'category' => $category->name,
                        'type' => $category->type,
                        'balance' => $category->getTotalBalance()
                    ];
                });
            
            return response()->json([
                'success' => true,
                'data' => [
                    'period' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate
                    ],
                    'summary' => $summary,
                    'fund_balances' => $fundBalances,
                    'distribution_efficiency' => $summary->total_collection > 0 
                        ? round(($summary->total_distribution / $summary->total_collection) * 100, 2)
                        : 0
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching financial summary',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
