<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuestionOption extends Model
{
    protected $fillable = [
        'question_id',
        'option_text',
        'option_key',
        'order',
        'formatting_options',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    /**
     * Get the question that owns the option.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the answer for this option (if it's a correct answer).
     */
    public function answer(): BelongsTo
    {
        return $this->belongsTo(QuestionAnswer::class, 'id', 'option_id');
    }

    /**
     * Get the images for the option.
     */
    public function images(): HasMany
    {
        return $this->hasMany(QuestionImage::class, 'option_id');
    }

    /**
     * Get the student answers for this option.
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class, 'option_id');
    }

    /**
     * Check if this option is a correct answer.
     */
    public function isCorrect(): bool
    {
        return $this->question->correctAnswers()
            ->where('option_id', $this->id)
            ->exists();
    }
}
