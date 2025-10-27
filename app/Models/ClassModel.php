<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Student;

class ClassModel extends Model
{
    protected $table = 'classes';

    protected $fillable = [
        'name',
        'code',
        'description',
        'academic_year',
        'level',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the teachers for this class
     */
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_classes', 'class_id', 'teacher_id')
                    ->withPivot(['subject_id', 'is_main_teacher'])
                    ->withTimestamps();
    }

    /**
     * Get the main teacher for this class
     */
    public function mainTeacher()
    {
        return $this->teachers()->wherePivot('is_main_teacher', true)->first();
    }

    /**
     * Get the students enrolled in this class
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'class_student', 'class_id', 'student_id')
                    ->withPivot('enrolled_at')
                    ->withTimestamps();
    }

    /**
     * Get the subjects taught in this class
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'class_subject', 'class_id', 'subject_id')
                    ->withPivot('teacher_id')
                    ->withTimestamps();
    }

    /**
     * Get the exams for this class
     */
    public function exams()
    {
        return $this->hasMany(Exam::class, 'class_id');
    }
}
