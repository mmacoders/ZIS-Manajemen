<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShariaFundCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'description',
        'type',
        'amil_percentage',
        'distribution_rules',
        'is_active',
        'is_baznas_compliant'
    ];

    protected $casts = [
        'distribution_rules' => 'array',
        'amil_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'is_baznas_compliant' => 'boolean'
    ];

    /**
     * Relationship to Sharia Accounts
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(ShariaAccount::class, 'fund_category_id');
    }

    /**
     * Relationship to Sharia Transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(ShariaTransaction::class, 'fund_category_id');
    }

    /**
     * Get total fund balance for this category
     */
    public function getTotalBalance(): float
    {
        return $this->accounts()->sum('current_balance');
    }

    /**
     * Get amil allocation amount
     */
    public function calculateAmilAmount(float $amount): float
    {
        return $amount * ($this->amil_percentage / 100);
    }

    /**
     * Get distributable amount (after amil deduction)
     */
    public function getDistributableAmount(float $amount): float
    {
        return $amount - $this->calculateAmilAmount($amount);
    }

    /**
     * Check if category follows BAZNAS standards
     */
    public function isBaznasCompliant(): bool
    {
        // BAZNAS standard amil percentage is max 12.5%
        return $this->is_baznas_compliant && $this->amil_percentage <= 12.5;
    }

    /**
     * Get distribution rules for 8 asnaf
     */
    public function getAsnafDistribution(): array
    {
        return $this->distribution_rules ?? [
            'fakir' => 25,
            'miskin' => 25,
            'amil' => 12.5,
            'muallaf' => 5,
            'riqab' => 5,
            'gharim' => 10,
            'fisabilillah' => 12.5,
            'ibnu_sabil' => 5
        ];
    }

    /**
     * Scope for active categories
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for BAZNAS compliant categories
     */
    public function scopeBaznasCompliant($query)
    {
        return $query->where('is_baznas_compliant', true);
    }

    /**
     * Scope by fund type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
