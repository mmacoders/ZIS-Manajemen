<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MLLearningData extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ml_learning_data';

    protected $fillable = [
        'data_type',
        'entity_type',
        'entity_id',
        'features',
        'labels',
        'weight',
        'is_training_data',
        'is_validated',
        'metadata',
        'data_period_start',
        'data_period_end',
        'created_by'
    ];

    protected $casts = [
        'features' => 'array',
        'labels' => 'array',
        'metadata' => 'array',
        'weight' => 'decimal:4',
        'is_training_data' => 'boolean',
        'is_validated' => 'boolean',
        'data_period_start' => 'datetime',
        'data_period_end' => 'datetime'
    ];

    public function entity()
    {
        return $this->morphTo();
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('data_type', $type);
    }

    public function scopeTrainingData($query)
    {
        return $query->where('is_training_data', true);
    }

    public function scopeValidated($query)
    {
        return $query->where('is_validated', true);
    }

    public function scopeForEntity($query, $entityType, $entityId = null)
    {
        $query->where('entity_type', $entityType);
        
        if ($entityId) {
            $query->where('entity_id', $entityId);
        }
        
        return $query;
    }
}
