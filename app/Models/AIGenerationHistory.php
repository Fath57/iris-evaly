<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AIGenerationHistory extends Model
{
    protected $table = 'ai_generation_history';

    protected $fillable = [
        'user_id',
        'prompt_id',
        'ai_provider',
        'subject_theme',
        'difficulty_level',
        'questions_requested',
        'questions_generated',
        'questions_accepted',
        'custom_prompt',
        'quality_rating',
        'feedback',
        'tokens_used',
        'cost',
        'status',
        'error_message',
    ];

    protected $casts = [
        'questions_requested' => 'integer',
        'questions_generated' => 'integer',
        'questions_accepted' => 'integer',
        'quality_rating' => 'decimal:2',
        'tokens_used' => 'integer',
        'cost' => 'decimal:6',
    ];

    /**
     * Get the user who requested the generation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the prompt used for generation.
     */
    public function prompt(): BelongsTo
    {
        return $this->belongsTo(AIPrompt::class, 'prompt_id');
    }

    /**
     * Scope a query to only include completed generations.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include failed generations.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to filter by AI provider.
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('ai_provider', $provider);
    }

    /**
     * Get the acceptance rate.
     */
    public function getAcceptanceRateAttribute(): float
    {
        if ($this->questions_generated === 0) {
            return 0;
        }
        
        return ($this->questions_accepted / $this->questions_generated) * 100;
    }
}
