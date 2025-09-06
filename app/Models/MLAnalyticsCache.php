<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class MLAnalyticsCache extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ml_analytics_cache';

    protected $fillable = [
        'cache_key',
        'analytics_type',
        'cache_data',
        'cache_metadata',
        'computed_at',
        'expires_at',
        'is_valid',
        'data_version',
        'hit_count',
        'last_accessed_at'
    ];

    protected $casts = [
        'cache_data' => 'array',
        'cache_metadata' => 'array',
        'computed_at' => 'datetime',
        'expires_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'is_valid' => 'boolean',
        'hit_count' => 'integer'
    ];

    public function scopeValid($query)
    {
        return $query->where('is_valid', true)
                    ->where('expires_at', '>', Carbon::now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('analytics_type', $type);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', Carbon::now());
    }

    public function isExpired(): bool
    {
        return $this->expires_at <= Carbon::now();
    }

    public function isValid(): bool
    {
        return $this->is_valid && !$this->isExpired();
    }

    public function incrementHit()
    {
        $this->increment('hit_count');
        $this->update(['last_accessed_at' => Carbon::now()]);
    }

    public static function getOrCompute(string $key, string $type, callable $computation, int $ttlMinutes = 60): array
    {
        $cache = static::valid()->where('cache_key', $key)->first();
        
        if ($cache) {
            $cache->incrementHit();
            return $cache->cache_data;
        }
        
        // Compute new data
        $data = $computation();
        
        // Store in cache
        static::create([
            'cache_key' => $key,
            'analytics_type' => $type,
            'cache_data' => $data,
            'computed_at' => Carbon::now(),
            'expires_at' => Carbon::now()->addMinutes($ttlMinutes),
            'is_valid' => true,
            'hit_count' => 1,
            'last_accessed_at' => Carbon::now()
        ]);
        
        return $data;
    }

    public static function invalidateByType(string $type): int
    {
        return static::where('analytics_type', $type)->update(['is_valid' => false]);
    }
}
