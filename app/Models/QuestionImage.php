<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionImage extends Model
{
    protected $fillable = [
        'question_id',
        'option_id',
        'image_path',
        'image_type',
        'alt_text',
        'width',
        'height',
        'order',
    ];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the question that owns the image.
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the option that owns the image.
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(QuestionOption::class);
    }

    /**
     * Get the full URL of the image.
     */
    public function getImageUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
