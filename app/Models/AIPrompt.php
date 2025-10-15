<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AIPrompt extends Model
{
    use SoftDeletes;

    protected $table = 'ai_prompts';

    protected $fillable = [
        'name',
        'description',
        'prompt_template',
        'ai_provider',
        'difficulty_level',
        'question_type',
        'created_by',
        'is_default',
        'is_active',
        'usage_count',
        'average_quality_rating',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
        'average_quality_rating' => 'decimal:2',
    ];

    /**
     * Get the user who created the prompt.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the generation history for this prompt.
     */
    public function generations(): HasMany
    {
        return $this->hasMany(AIGenerationHistory::class, 'prompt_id');
    }

    /**
     * Scope a query to only include active prompts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default prompts.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope a query to filter by AI provider.
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('ai_provider', $provider);
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Update average quality rating.
     */
    public function updateQualityRating(float $newRating): void
    {
        $totalGenerations = $this->generations()->whereNotNull('quality_rating')->count();
        
        if ($totalGenerations === 0) {
            $this->average_quality_rating = $newRating;
        } else {
            $currentAverage = $this->average_quality_rating ?? 0;
            $this->average_quality_rating = (($currentAverage * $totalGenerations) + $newRating) / ($totalGenerations + 1);
        }
        
        $this->save();
    }
}
