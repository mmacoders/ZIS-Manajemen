<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShariaTransaction;
use App\Models\ShariaAccount;
use App\Models\ShariaFundCategory;
use App\Models\ZisTransaction;
use App\Models\Distribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ShariaAccountingController extends Controller
{
    /**
     * Get dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
            $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());

            // Financial summary
            $financialSummary = $this->getFinancialSummary($startDate, $endDate);
            
            // Recent transactions
            $recentTransactions = ShariaTransaction::with([
                'fundCategory:id,name',
                'debitAccount:id,account_code,account_name',
                'creditAccount:id,account_code,account_name'
            ])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
            // Fund categories with balances
            $fundCategories = ShariaFundCategory::withCount([
                'transactions as transaction_count',
                'accounts as account_count'
            ])->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'financial_summary' => $financialSummary,
                    'recent_transactions' => $recentTransactions,
                    'fund_categories' => $fundCategories
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
     * Get financial summary
     */
    public function getFinancialSummary($startDate, $endDate): array
    {
        // Total collections
        $totalCollections = ShariaTransaction::where('transaction_type', 'collection')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        // Total distributions
        $totalDistributions = ShariaTransaction::where('transaction_type', 'distribution')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amount');
            
        // Net balance
        $netBalance = $totalCollections - $totalDistributions;
        
        // Amil income
        $amilIncome = ShariaTransaction::where('transaction_type', 'collection')
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->sum('amil_amount');
            
        return [
            'total_collections' => $totalCollections,
            'total_distributions' => $totalDistributions,
            'net_balance' => $netBalance,
            'amil_income' => $amilIncome
        ];
    }
    
    /**
     * Get fund categories
     */
    public function getFundCategories(): JsonResponse
    {
        try {
            $categories = ShariaFundCategory::all();
            
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
    public function getChartOfAccounts(Request $request): JsonResponse
    {
        try {
            $categoryId = $request->input('fund_category_id');
            $perPage = $request->input('per_page', 15);
            
            $query = ShariaAccount::with('fundCategory:id,name');
            
            if ($categoryId) {
                $query->where('fund_category_id', $categoryId);
            }
            
            $accounts = $query->orderBy('account_code')
                ->paginate($perPage);
            
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
     * Get transactions
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
                'donatur:id,nama',
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
                'donatur_id' => $zisTransaction->donatur_id,
                'description' => 'Penerimaan ' . $fundCategory->name . ' dari ' . $zisTransaction->donatur->nama,
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
                    'donatur'
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
                    'mustahiq'
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
}