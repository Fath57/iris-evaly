<?php

namespace App\Repositories;

use App\Interfaces\ExamAttemptRepositoryInterface;
use App\Models\ExamAttempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ExamAttemptRepository extends BaseRepository implements ExamAttemptRepositoryInterface
{
    public function __construct(ExamAttempt $model)
    {
        parent::__construct($model);
    }

    public function getAttemptsByExam(int $examId)
    {
        try {
            return $this->model->where('exam_id', $examId)
                ->with(['student', 'answers'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching attempts by exam: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAttemptsByStudent(int $studentId)
    {
        try {
            return $this->model->where('student_id', $studentId)
                ->with(['exam', 'exam.subject'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching attempts by student: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAttemptWithAnswers(int $attemptId)
    {
        try {
            return $this->model->with([
                'answers',
                'answers.question',
                'answers.option',
                'exam',
                'exam.questions',
            ])->findOrFail($attemptId);
        } catch (\Exception $e) {
            Log::error('Error fetching attempt with answers: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStudentAttempts(int $studentId, int $examId)
    {
        try {
            return $this->model->where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching student attempts: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getInProgressAttempt(int $studentId, int $examId)
    {
        try {
            return $this->model->where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->where('status', 'in_progress')
                ->first();
        } catch (\Exception $e) {
            Log::error('Error fetching in-progress attempt: ' . $e->getMessage());
            throw $e;
        }
    }

    public function completeAttempt(int $attemptId, array $data)
    {
        try {
            $attempt = $this->findById($attemptId);
            $attempt->completed_at = now();
            $attempt->status = 'completed';
            $attempt->time_spent_seconds = $data['time_spent_seconds'] ?? 0;
            $attempt->save();
            
            Log::info("Attempt completed", ['attempt_id' => $attemptId]);
            
            return $attempt;
        } catch (\Exception $e) {
            Log::error('Error completing attempt: ' . $e->getMessage());
            throw $e;
        }
    }

    public function calculateScore(int $attemptId)
    {
        try {
            $attempt = $this->getAttemptWithAnswers($attemptId);
            
            $totalPoints = $attempt->answers->sum('points_awarded');
            $maxPoints = $attempt->exam->total_points;
            $percentage = $maxPoints > 0 ? ($totalPoints / $maxPoints) * 100 : 0;
            
            $attempt->score = $totalPoints;
            $attempt->percentage = $percentage;
            $attempt->save();
            
            Log::info("Attempt score calculated", [
                'attempt_id' => $attemptId,
                'score' => $totalPoints,
                'percentage' => $percentage
            ]);
            
            return $attempt;
        } catch (\Exception $e) {
            Log::error('Error calculating attempt score: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamStatistics(int $examId)
    {
        try {
            $attempts = $this->model->completed()
                ->where('exam_id', $examId)
                ->get();
            
            return [
                'total_attempts' => $attempts->count(),
                'average_score' => $attempts->avg('score'),
                'average_percentage' => $attempts->avg('percentage'),
                'highest_score' => $attempts->max('score'),
                'lowest_score' => $attempts->min('score'),
                'pass_rate' => $attempts->filter(function ($attempt) {
                    return $attempt->hasPassed();
                })->count() / max($attempts->count(), 1) * 100,
                'average_time' => $attempts->avg('time_spent_seconds'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting exam statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStudentStatistics(int $studentId)
    {
        try {
            $attempts = $this->model->completed()
                ->where('student_id', $studentId)
                ->get();
            
            return [
                'total_exams' => $attempts->count(),
                'average_score' => $attempts->avg('percentage'),
                'exams_passed' => $attempts->filter(function ($attempt) {
                    return $attempt->hasPassed();
                })->count(),
                'exams_failed' => $attempts->filter(function ($attempt) {
                    return !$attempt->hasPassed();
                })->count(),
                'total_time_spent' => $attempts->sum('time_spent_seconds'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting student statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}

