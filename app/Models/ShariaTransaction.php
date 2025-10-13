<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class ShariaTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'transaction_type',
        'fund_category_id',
        'debit_account_id',
        'credit_account_id',
        'amount',
        'amil_amount',
        'reference_type',
        'reference_id',
        'donatur_id',
        'mustahiq_id',
        'mustahiq_category',
        'description',
        'baznas_notes',
        'is_baznas_compliant',
        'compliance_data',
        'status',
        'approved_by',
        'approved_at',
        'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'amil_amount' => 'decimal:2',
        'compliance_data' => 'array',
        'is_baznas_compliant' => 'boolean',
        'approved_at' => 'datetime'
    ];

    /**
     * Relationship to Fund Category
     */
    public function fundCategory(): BelongsTo
    {
        return $this->belongsTo(ShariaFundCategory::class, 'fund_category_id');
    }

    /**
     * Relationship to Debit Account
     */
    public function debitAccount(): BelongsTo
    {
        return $this->belongsTo(ShariaAccount::class, 'debit_account_id');
    }

    /**
     * Relationship to Credit Account
     */
    public function creditAccount(): BelongsTo
    {
        return $this->belongsTo(ShariaAccount::class, 'credit_account_id');
    }

    /**
     * Relationship to Donatur
     */
    public function donatur(): BelongsTo
    {
        return $this->belongsTo(Donatur::class, 'donatur_id');
    }

    /**
     * Relationship to Mustahiq
     */
    public function mustahiq(): BelongsTo
    {
        return $this->belongsTo(Mustahiq::class, 'mustahiq_id');
    }

    /**
     * Relationship to Creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to Approver
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Generate transaction number
     */
    public static function generateTransactionNumber(): string
    {
        $prefix = 'SHT';
        $date = now()->format('Ymd');
        $sequence = str_pad(
            self::whereDate('created_at', today())->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );
        
        return $prefix . $date . $sequence;
    }

    /**
     * Calculate amil amount based on fund category
     */
    public function calculateAmilAmount(): void
    {
        if ($this->fundCategory) {
            $this->amil_amount = $this->fundCategory->calculateAmilAmount($this->amount);
        }
    }

    /**
     * Post transaction (update account balances)
     */
    public function postTransaction(): bool
    {
        if ($this->status === 'posted') {
            return false; // Already posted
        }

        DB::transaction(function () {
            // Update debit account
            $this->debitAccount->updateBalance($this->amount, 'debit');
            
            // Update credit account
            $this->creditAccount->updateBalance($this->amount, 'credit');
            
            // Update status
            $this->update(['status' => 'posted']);
        });

        return true;
    }

    /**
     * Reverse transaction
     */
    public function reverseTransaction(): bool
    {
        if ($this->status !== 'posted') {
            return false;
        }

        DB::transaction(function () {
            // Reverse debit account
            $this->debitAccount->updateBalance($this->amount, 'credit');
            
            // Reverse credit account
            $this->creditAccount->updateBalance($this->amount, 'debit');
            
            // Create reversal transaction
            self::create([
                'transaction_number' => self::generateTransactionNumber(),
                'transaction_date' => now(),
                'transaction_type' => 'adjustment',
                'fund_category_id' => $this->fund_category_id,
                'debit_account_id' => $this->credit_account_id,
                'credit_account_id' => $this->debit_account_id,
                'amount' => $this->amount,
                'description' => 'Reversal of transaction ' . $this->transaction_number,
                'reference_type' => get_class($this),
                'reference_id' => $this->id,
                'created_by' => auth()->id(),
                'status' => 'posted'
            ]);
        });

        return true;
    }

    /**
     * Check BAZNAS compliance
     */
    public function checkBaznasCompliance(): array
    {
        $compliance = [
            'is_compliant' => true,
            'issues' => []
        ];

        // Check amil percentage
        if ($this->amil_amount > ($this->amount * 0.125)) {
            $compliance['is_compliant'] = false;
            $compliance['issues'][] = 'Amil amount exceeds 12.5% limit';
        }

        // Check transaction type consistency
        if ($this->transaction_type === 'collection' && $this->mustahiq_id) {
            $compliance['is_compliant'] = false;
            $compliance['issues'][] = 'Collection transaction should not have mustahiq';
        }

        if ($this->transaction_type === 'distribution' && $this->donatur_id) {
            $compliance['is_compliant'] = false;
            $compliance['issues'][] = 'Distribution transaction should not have donatur';
        }

        // Check for required references
        if (!$this->reference_type || !$this->reference_id) {
            $compliance['is_compliant'] = false;
            $compliance['issues'][] = 'Missing transaction reference';
        }

        $this->update([
            'is_baznas_compliant' => $compliance['is_compliant'],
            'compliance_data' => $compliance
        ]);

        return $compliance;
    }

    /**
     * Scope by date range
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Scope by transaction type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }
}