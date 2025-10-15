<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the classes that teach this subject
     */
    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_subject', 'subject_id', 'class_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    /**
     * Get the exams for this subject
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'subject_id');
    }
}
