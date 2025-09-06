<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShariaAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_code',
        'account_name',
        'account_name_ar',
        'fund_category_id',
        'account_type',
        'normal_balance',
        'parent_code',
        'level',
        'opening_balance',
        'current_balance',
        'is_active',
        'is_baznas_required',
        'baznas_mapping',
        'description'
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'level' => 'integer',
        'is_active' => 'boolean',
        'is_baznas_required' => 'boolean',
        'baznas_mapping' => 'array'
    ];

    /**
     * Relationship to Fund Category
     */
    public function fundCategory(): BelongsTo
    {
        return $this->belongsTo(ShariaFundCategory::class, 'fund_category_id');
    }

    /**
     * Relationship to parent account
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_code', 'account_code');
    }

    /**
     * Relationship to child accounts
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_code', 'account_code');
    }

    /**
     * Debit transactions
     */
    public function debitTransactions(): HasMany
    {
        return $this->hasMany(ShariaTransaction::class, 'debit_account_id');
    }

    /**
     * Credit transactions
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(ShariaTransaction::class, 'credit_account_id');
    }

    /**
     * Update account balance based on transaction
     */
    public function updateBalance(float $amount, string $type): void
    {
        if ($this->normal_balance === 'debit') {
            if ($type === 'debit') {
                $this->current_balance += $amount;
            } else {
                $this->current_balance -= $amount;
            }
        } else {
            if ($type === 'credit') {
                $this->current_balance += $amount;
            } else {
                $this->current_balance -= $amount;
            }
        }
        
        $this->save();
    }

    /**
     * Get account hierarchy path
     */
    public function getHierarchyPath(): string
    {
        $path = [$this->account_name];
        $current = $this;
        
        while ($current->parent) {
            $current = $current->parent;
            array_unshift($path, $current->account_name);
        }
        
        return implode(' > ', $path);
    }

    /**
     * Check if account is a control account (has children)
     */
    public function isControlAccount(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get account balance for BAZNAS reporting
     */
    public function getBaznasBalance(): array
    {
        return [
            'account_code' => $this->account_code,
            'account_name' => $this->account_name,
            'balance' => $this->current_balance,
            'baznas_mapping' => $this->baznas_mapping,
            'fund_category' => $this->fundCategory->name ?? ''
        ];
    }

    /**
     * Scope for active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for BAZNAS required accounts
     */
    public function scopeBaznasRequired($query)
    {
        return $query->where('is_baznas_required', true);
    }

    /**
     * Scope by account type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('account_type', $type);
    }

    /**
     * Scope by fund category
     */
    public function scopeByFundCategory($query, int $categoryId)
    {
        return $query->where('fund_category_id', $categoryId);
    }
}
