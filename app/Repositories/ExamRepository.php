<?php

namespace App\Repositories;

use App\Interfaces\ExamRepositoryInterface;
use App\Models\Exam;
use Illuminate\Support\Facades\Log;

class ExamRepository extends BaseRepository implements ExamRepositoryInterface
{
    public function __construct(Exam $model)
    {
        parent::__construct($model);
    }

    public function getPublishedExams()
    {
        try {
            return $this->model->published()
                ->with(['subject', 'class', 'creator'])
                ->orderBy('start_date', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching published exams: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getOngoingExams()
    {
        try {
            return $this->model->ongoing()
                ->with(['subject', 'class', 'creator'])
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching ongoing exams: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDraftExams()
    {
        try {
            return $this->model->draft()
                ->with(['subject', 'class', 'creator'])
                ->orderBy('updated_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching draft exams: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamsBySubject(int $subjectId)
    {
        try {
            return $this->model->where('subject_id', $subjectId)
                ->with(['class', 'creator'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching exams by subject: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamsByClass(int $classId)
    {
        try {
            return $this->model->where('class_id', $classId)
                ->with(['subject', 'creator'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching exams by class: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamsCreatedBy(int $userId)
    {
        try {
            return $this->model->where('created_by', $userId)
                ->with(['subject', 'class'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching exams by creator: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamWithQuestions(int $examId)
    {
        try {
            return $this->model->with([
                'questions' => function ($query) {
                    $query->orderBy('order');
                },
                'questions.options' => function ($query) {
                    $query->orderBy('order');
                },
                'questions.correctAnswers',
                'questions.images',
                'questions.options.images',
            ])->findOrFail($examId);
        } catch (\Exception $e) {
            Log::error('Error fetching exam with questions: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getExamWithAttempts(int $examId)
    {
        try {
            return $this->model->with([
                'attempts' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                },
                'attempts.student',
                'attempts.answers',
            ])->findOrFail($examId);
        } catch (\Exception $e) {
            Log::error('Error fetching exam with attempts: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateExamStatus(int $examId, string $status)
    {
        try {
            $exam = $this->findById($examId);
            $exam->status = $status;
            $exam->save();
            
            Log::info("Exam status updated", ['exam_id' => $examId, 'status' => $status]);
            
            return $exam;
        } catch (\Exception $e) {
            Log::error('Error updating exam status: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAvailableExamsForStudent(int $studentId, int $classId)
    {
        try {
            return $this->model->published()
                ->where('class_id', $classId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->whereHas('attempts', function ($query) use ($studentId) {
                    $query->where('student_id', $studentId)
                        ->where('status', 'completed');
                }, '<', function ($exam) {
                    return $exam->max_attempts;
                })
                ->with(['subject', 'attempts' => function ($query) use ($studentId) {
                    $query->where('student_id', $studentId);
                }])
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching available exams for student: ' . $e->getMessage());
            throw $e;
        }
    }

    public function calculateTotalPoints(int $examId)
    {
        try {
            $exam = $this->model->with('questions')->findOrFail($examId);
            $totalPoints = $exam->questions->sum('points');
            
            $exam->total_points = $totalPoints;
            $exam->save();
            
            Log::info("Exam total points calculated", ['exam_id' => $examId, 'total_points' => $totalPoints]);
            
            return $totalPoints;
        } catch (\Exception $e) {
            Log::error('Error calculating exam total points: ' . $e->getMessage());
            throw $e;
        }
    }
}

