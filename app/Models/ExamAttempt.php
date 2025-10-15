<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamAttempt extends Model
{
    protected $fillable = [
        'exam_id',
        'student_id',
        'started_at',
        'completed_at',
        'score',
        'percentage',
        'status',
        'time_spent_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'time_spent_seconds' => 'integer',
    ];

    /**
     * Get the exam that owns the attempt.
     */
    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Get the student who made the attempt.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the answers for the attempt.
     */
    public function answers(): HasMany
    {
        return $this->hasMany(ExamAttemptAnswer::class);
    }

    /**
     * Scope a query to only include completed attempts.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include in-progress attempts.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Check if the attempt has passed.
     */
    public function hasPassed(): bool
    {
        return $this->percentage >= $this->exam->passing_score;
    }

    /**
     * Calculate the time remaining in seconds.
     */
    public function timeRemaining(): int
    {
        if ($this->status !== 'in_progress') {
            return 0;
        }

        $elapsed = now()->diffInSeconds($this->started_at);
        $allowed = $this->exam->duration_minutes * 60;
        
        return max(0, $allowed - $elapsed);
    }
}
