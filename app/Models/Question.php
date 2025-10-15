<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'exam_id',
        'type',
        'question_text',
        'points',
        'order',
        'explanation',
        'feedback',
        'category',
        'difficulty_level',
        'is_in_bank',
        'formatting_options',
    ];

    protected $casts = [
        'is_in_bank' => 'boolean',
        'points' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the exam that owns the question.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the options for the question.
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    /**
     * Get the correct answers for the question.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class);
    }

    /**
     * Get the correct answers for the question.
     */
    public function correctAnswers(): HasMany
    {
        return $this->hasMany(QuestionAnswer::class)->where('is_correct', true);
    }

    /**
     * Get the images for the question.
     */
    public function images(): HasMany
    {
        return $this->hasMany(QuestionImage::class);
    }

    /**
     * Get the student answers for the question.
     */
    public function studentAnswers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    /**
     * Scope a query to only include questions in the bank.
     */
    public function scopeInBank($query)
    {
        return $query->where('is_in_bank', true);
    }

    /**
     * Scope a query to filter by difficulty level.
     */
    public function scopeByDifficulty($query, $level)
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
