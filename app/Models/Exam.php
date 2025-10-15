<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exam extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'instructions',
        'subject_id',
        'class_id',
        'created_by',
        'duration_minutes',
        'total_points',
        'passing_score',
        'start_date',
        'end_date',
        'status',
        'shuffle_questions',
        'shuffle_options',
        'show_results_immediately',
        'allow_review',
        'show_correct_answers',
        'require_all_questions',
        'questions_per_page',
        'allow_navigation',
        'max_attempts',
        'success_message',
        'failure_message',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'shuffle_questions' => 'boolean',
        'shuffle_options' => 'boolean',
        'show_results_immediately' => 'boolean',
        'allow_review' => 'boolean',
        'show_correct_answers' => 'boolean',
        'require_all_questions' => 'boolean',
        'allow_navigation' => 'boolean',
        'duration_minutes' => 'integer',
        'total_points' => 'integer',
        'passing_score' => 'integer',
        'max_attempts' => 'integer',
        'questions_per_page' => 'integer',
    ];

    /**
     * Get the subject that owns the exam.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the class that owns the exam.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get the user who created the exam.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the questions for the exam.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    /**
     * Get the attempts for the exam.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    /**
     * Scope a query to only include published exams.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope a query to only include ongoing exams.
     */
    public function scopeOngoing($query)
    {
        return $query->where('status', 'ongoing')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    /**
     * Scope a query to only include draft exams.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Check if the exam is currently available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'published' 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    /**
     * Check if a student can take the exam.
     */
    public function canBeTakenBy(User $student): bool
    {
        $attemptCount = $this->attempts()
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        return $this->isAvailable() && $attemptCount < $this->max_attempts;
    }
}
