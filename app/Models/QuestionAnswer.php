<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionAnswer extends Model
{
    protected $fillable = [
        'question_id',
        'option_id',
        'answer_text',
        'is_correct',
        'explanation',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Get the question that owns the answer.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the option associated with this answer.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }
}
