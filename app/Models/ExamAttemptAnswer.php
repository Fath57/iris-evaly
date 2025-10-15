<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamAttemptAnswer extends Model
{
    protected $fillable = [
        'exam_attempt_id',
        'question_id',
        'option_id',
        'answer_text',
        'is_correct',
        'points_awarded',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'decimal:2',
        'time_spent_seconds' => 'integer',
    ];

    /**
     * Get the exam attempt that owns the answer.
     */
    public function examAttempt(): BelongsTo
    {
        return $this->belongsTo(ExamAttempt::class);
    }

    /**
     * Get the question that this answer belongs to.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the option selected (for multiple choice questions).
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
