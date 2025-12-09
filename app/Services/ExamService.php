<?php

namespace App\Services;

use App\Interfaces\ExamRepositoryInterface;
use App\Interfaces\QuestionRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamService
{
    protected $examRepository;
    protected $questionRepository;

    public function __construct(
        ExamRepositoryInterface $examRepository,
        QuestionRepositoryInterface $questionRepository
    ) {
        $this->examRepository = $examRepository;
        $this->questionRepository = $questionRepository;
    }

    /**
     * Create a new exam.
     */
    public function createExam(array $data)
    {
        try {
            DB::beginTransaction();

            $exam = $this->examRepository->create($data);

            Log::info("Exam created", ['exam_id' => $exam->id]);

            DB::commit();
            return $exam;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing exam.
     */
    public function updateExam(int $examId, array $data)
    {
        try {
            DB::beginTransaction();

            $exam = $this->examRepository->update($examId, $data);

            // Recalculate total points if questions exist
            if ($exam->questions()->count() > 0) {
                $this->examRepository->calculateTotalPoints($examId);
            }

            Log::info("Exam updated", ['exam_id' => $examId]);

            DB::commit();
            return $exam;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an exam.
     */
    public function deleteExam(int $examId)
    {
        try {
            $this->examRepository->delete($examId);
            Log::info("Exam deleted", ['exam_id' => $examId]);
        } catch (\Exception $e) {
            Log::error('Error deleting exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exams for a student categorized by status
     *
     * @param int $studentId
     * @return array
     */
    public function getStudentExams(int $studentId): array
    {
        try {
            $student = \App\Models\Student::with('classes')->findOrFail($studentId);
            $classIds = $student->classes->pluck('id')->toArray();

            if (empty($classIds)) {
                return [
                    'upcoming' => [],
                    'ongoing' => [],
                    'past' => []
                ];
            }

            $now = now();

            // Examens à venir (publiés, date de début future)
            $upcoming = \App\Models\Exam::whereIn('class_id', $classIds)
                ->where('status', 'published')
                ->where('start_date', '>', $now)
                ->with(['subject', 'class', 'questions'])
                ->orderBy('start_date', 'asc')
                ->get()
                ->map(function ($exam) use ($studentId) {
                    return $this->formatExamForStudent($exam, $studentId);
                });

            // Examens en cours (publiés, entre start_date et end_date)
            $ongoing = \App\Models\Exam::whereIn('class_id', $classIds)
                ->where('status', 'published')
                ->where('start_date', '<=', $now)
                ->where('end_date', '>=', $now)
                ->with(['subject', 'class', 'questions'])
                ->orderBy('start_date', 'asc')
                ->get()
                ->map(function ($exam) use ($studentId) {
                    return $this->formatExamForStudent($exam, $studentId);
                });

            // Examens passés (publiés, date de fin dépassée)
            $past = \App\Models\Exam::whereIn('class_id', $classIds)
                ->where('status', 'published')
                ->where('end_date', '<', $now)
                ->with(['subject', 'class', 'questions'])
                ->orderBy('end_date', 'desc')
                ->get()
                ->map(function ($exam) use ($studentId) {
                    return $this->formatExamForStudent($exam, $studentId);
                });

            return [
                'upcoming' => $upcoming,
                'ongoing' => $ongoing,
                'past' => $past
            ];
        } catch (\Exception $e) {
            Log::error('Error getting student exams: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Format exam data for student with attempt information
     *
     * @param \App\Models\Exam $exam
     * @param int $studentId
     * @return array
     */
    private function formatExamForStudent($exam, int $studentId): array
    {
        $attempts = \App\Models\ExamAttempt::where('exam_id', $exam->id)
            ->where('student_id', $studentId)
            ->get();

        $attemptCount = $attempts->count();
        $maxAttempts = $exam->max_attempts ?? 0;
        $canAttempt = $maxAttempts === 0 || $attemptCount < $maxAttempts;

        $bestScore = $attempts->max('score');
        $lastAttempt = $attempts->sortByDesc('created_at')->first();

        return [
            'id' => $exam->id,
            'show_results_immediately' => $exam->show_results_immediately,
            'title' => $exam->title,
            'description' => $exam->description,
            'subject' => [
                'id' => $exam->subject->id,
                'name' => $exam->subject->name,
            ],
            'class' => [
                'id' => $exam->class->id,
                'name' => $exam->class->name,
            ],
            'duration' => $exam->duration,
            'total_points' => $exam->total_points,
            'passing_score' => $exam->passing_score,
            'start_date' => $exam->start_date->toISOString(),
            'end_date' => $exam->end_date->toISOString(),
            'questions_count' => $exam->questions->count(),
            'max_attempts' => $maxAttempts,
            'attempt_count' => $attemptCount,
            'can_attempt' => $canAttempt,
            'best_score' => $bestScore,
            'last_attempt' => $lastAttempt ? [
                'id' => $lastAttempt->id,
                'score' => $lastAttempt->score,
                'completed_at' => $lastAttempt->completed_at?->toISOString(),
                'status' => $lastAttempt->status,
            ] : null,
        ];
    }

    /**
     * Publish an exam.
     */
    public function publishExam(int $examId)
    {
        try {
            $exam = $this->examRepository->getExamWithQuestions($examId);

            // Validate exam before publishing
            if ($exam->questions->count() === 0) {
                throw new \Exception('Cannot publish exam without questions');
            }

            // Recalculate total points
            $this->examRepository->calculateTotalPoints($examId);

            // Update status to published
            $exam = $this->examRepository->updateExamStatus($examId, 'published');

            Log::info("Exam published", ['exam_id' => $examId]);

            return $exam;
        } catch (\Exception $e) {
            Log::error('Error publishing exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Archive an exam.
     */
    public function archiveExam(int $examId)
    {
        try {
            $exam = $this->examRepository->updateExamStatus($examId, 'archived');
            Log::info("Exam archived", ['exam_id' => $examId]);
            return $exam;
        } catch (\Exception $e) {
            Log::error('Error archiving exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exam details with all related data.
     */
    public function getExamDetails(int $examId)
    {
        try {
            return $this->examRepository->getExamWithQuestions($examId);
        } catch (\Exception $e) {
            Log::error('Error fetching exam details: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available exams for a student.
     */
    public function getAvailableExamsForStudent(int $studentId, int $classId)
    {
        try {
            return $this->examRepository->getAvailableExamsForStudent($studentId, $classId);
        } catch (\Exception $e) {
            Log::error('Error fetching available exams: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exams by teacher.
     */
    public function getExamsByTeacher(int $teacherId)
    {
        try {
            return $this->examRepository->getExamsCreatedBy($teacherId);
        } catch (\Exception $e) {
            Log::error('Error fetching exams by teacher: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exams by class.
     */
    public function getExamsByClass(int $classId)
    {
        try {
            return $this->examRepository->getExamsByClass($classId);
        } catch (\Exception $e) {
            Log::error('Error fetching exams by class: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exams by subject.
     */
    public function getExamsBySubject(int $subjectId)
    {
        try {
            return $this->examRepository->getExamsBySubject($subjectId);
        } catch (\Exception $e) {
            Log::error('Error fetching exams by subject: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Duplicate an exam.
     */
    public function duplicateExam(int $examId, array $newData = [])
    {
        try {
            DB::beginTransaction();

            $originalExam = $this->examRepository->getExamWithQuestions($examId);

            // Create new exam with modified data
            $examData = $originalExam->toArray();
            unset($examData['id'], $examData['created_at'], $examData['updated_at']);

            $examData['title'] = $newData['title'] ?? $examData['title'] . ' (Copy)';
            $examData['status'] = 'draft';

            $newExam = $this->examRepository->create($examData);

            // Duplicate all questions
            foreach ($originalExam->questions as $question) {
                $this->questionRepository->importQuestionToExam($question->id, $newExam->id);
            }

            // Recalculate total points
            $this->examRepository->calculateTotalPoints($newExam->id);

            Log::info("Exam duplicated", [
                'original_id' => $examId,
                'new_id' => $newExam->id
            ]);

            DB::commit();
            return $newExam;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exam statistics.
     */
    public function getExamStatistics(int $examId)
    {
        try {
            $exam = $this->examRepository->getExamWithAttempts($examId);

            $statistics = [
                'exam' => $exam,
                'total_questions' => $exam->questions()->count(),
                'total_points' => $exam->total_points,
                'total_attempts' => $exam->attempts()->count(),
                'completed_attempts' => $exam->attempts()->completed()->count(),
                'average_score' => $exam->attempts()->completed()->avg('score'),
                'average_percentage' => $exam->attempts()->completed()->avg('percentage'),
                'highest_score' => $exam->attempts()->completed()->max('score'),
                'lowest_score' => $exam->attempts()->completed()->min('score'),
            ];

            return $statistics;
        } catch (\Exception $e) {
            Log::error('Error getting exam statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}

