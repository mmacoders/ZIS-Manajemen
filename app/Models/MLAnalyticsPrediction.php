<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MLAnalyticsPrediction extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ml_analytics_predictions';

    protected $fillable = [
        'prediction_type',
        'model_type',
        'target_id',
        'target_type',
        'input_data',
        'prediction_result',
        'confidence_score',
        'accuracy_score',
        'prediction_date',
        'model_parameters',
        'is_verified',
        'actual_value',
        'verified_at',
        'created_by'
    ];

    protected $casts = [
        'input_data' => 'array',
        'prediction_result' => 'array',
        'model_parameters' => 'array',
        'confidence_score' => 'decimal:2',
        'accuracy_score' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'is_verified' => 'boolean',
        'prediction_date' => 'date',
        'verified_at' => 'datetime'
    ];

    public function target()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('prediction_type', $type);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    public function scopeHighConfidence($query, $threshold = 80)
    {
        return $query->where('confidence_score', '>=', $threshold);
    }
}
