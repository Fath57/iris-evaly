<?php

namespace App\Services;

use App\Interfaces\ExamAttemptRepositoryInterface;
use App\Interfaces\ExamRepositoryInterface;
use App\Models\ExamAttemptAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExamAttemptService
{
    protected $attemptRepository;
    protected $examRepository;

    public function __construct(
        ExamAttemptRepositoryInterface $attemptRepository,
        ExamRepositoryInterface $examRepository
    ) {
        $this->attemptRepository = $attemptRepository;
        $this->examRepository = $examRepository;
    }

    /**
     * Start a new exam attempt.
     */
    public function startExam(int $examId, int $studentId)
    {
        try {
            DB::beginTransaction();

            // Check if exam is available
            $exam = $this->examRepository->findById($examId);
            
            if (!$exam->isAvailable()) {
                throw new \Exception('Exam is not available at this time');
            }

            // Check if student can take the exam
            $previousAttempts = $this->attemptRepository->getStudentAttempts($studentId, $examId);
            $completedAttempts = $previousAttempts->where('status', 'completed')->count();

            if ($completedAttempts >= $exam->max_attempts) {
                throw new \Exception('Maximum number of attempts reached');
            }

            // Check for existing in-progress attempt
            $inProgressAttempt = $this->attemptRepository->getInProgressAttempt($studentId, $examId);
            if ($inProgressAttempt) {
                DB::commit();
                return $inProgressAttempt;
            }

            // Create new attempt
            $attempt = $this->attemptRepository->create([
                'exam_id' => $examId,
                'student_id' => $studentId,
                'started_at' => now(),
                'status' => 'in_progress',
            ]);

            Log::info("Exam attempt started", [
                'attempt_id' => $attempt->id,
                'exam_id' => $examId,
                'student_id' => $studentId
            ]);

            DB::commit();
            return $attempt;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Submit answer for a question.
     */
    public function submitAnswer(int $attemptId, array $answerData)
    {
        try {
            DB::beginTransaction();

            $attempt = $this->attemptRepository->findById($attemptId);

            if ($attempt->status !== 'in_progress') {
                throw new \Exception('Cannot submit answer for completed attempt');
            }

            // Check if answer already exists
            $existingAnswer = ExamAttemptAnswer::where('exam_attempt_id', $attemptId)
                ->where('question_id', $answerData['question_id'])
                ->first();

            if ($existingAnswer) {
                // Update existing answer
                $existingAnswer->update([
                    'option_id' => $answerData['option_id'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'time_spent_seconds' => $answerData['time_spent_seconds'] ?? 0,
                ]);
                $answer = $existingAnswer;
            } else {
                // Create new answer
                $answer = ExamAttemptAnswer::create([
                    'exam_attempt_id' => $attemptId,
                    'question_id' => $answerData['question_id'],
                    'option_id' => $answerData['option_id'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'time_spent_seconds' => $answerData['time_spent_seconds'] ?? 0,
                ]);
            }

            Log::info("Answer submitted", [
                'attempt_id' => $attemptId,
                'question_id' => $answerData['question_id'],
                'answer_id' => $answer->id
            ]);

            DB::commit();
            return $answer;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting answer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Complete exam attempt and calculate score.
     */
    public function completeExam(int $attemptId, array $data = [])
    {
        try {
            DB::beginTransaction();

            $attempt = $this->attemptRepository->getAttemptWithAnswers($attemptId);

            if ($attempt->status !== 'in_progress') {
                throw new \Exception('Attempt is not in progress');
            }

            // Calculate time spent
            $timeSpent = $data['time_spent_seconds'] ?? now()->diffInSeconds($attempt->started_at);

            // Grade all answers
            $this->gradeAllAnswers($attemptId);

            // Complete the attempt
            $attempt = $this->attemptRepository->completeAttempt($attemptId, [
                'time_spent_seconds' => $timeSpent,
            ]);

            // Calculate final score
            $attempt = $this->attemptRepository->calculateScore($attemptId);

            Log::info("Exam attempt completed", [
                'attempt_id' => $attemptId,
                'score' => $attempt->score,
                'percentage' => $attempt->percentage
            ]);

            DB::commit();
            return $attempt;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error completing exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Grade all answers for an attempt.
     */
    protected function gradeAllAnswers(int $attemptId)
    {
        try {
            $attempt = $this->attemptRepository->getAttemptWithAnswers($attemptId);

            foreach ($attempt->answers as $answer) {
                $question = $answer->question;
                $isCorrect = false;
                $pointsAwarded = 0;

                switch ($question->type) {
                    case 'multiple_choice':
                    case 'true_false':
                        // Check if selected option is correct
                        if ($answer->option_id) {
                            $isCorrect = $question->correctAnswers()
                                ->where('option_id', $answer->option_id)
                                ->exists();
                            $pointsAwarded = $isCorrect ? $question->points : 0;
                        }
                        break;

                    case 'short_answer':
                        // For short answers, manual grading might be needed
                        // For now, we'll mark as needs grading
                        $isCorrect = null;
                        $pointsAwarded = 0;
                        break;

                    case 'essay':
                        // Essays require manual grading
                        $isCorrect = null;
                        $pointsAwarded = 0;
                        break;
                }

                // Update answer with grading results
                $answer->update([
                    'is_correct' => $isCorrect,
                    'points_awarded' => $pointsAwarded,
                ]);
            }

            Log::info("Answers graded", ['attempt_id' => $attemptId]);
        } catch (\Exception $e) {
            Log::error('Error grading answers: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Manually grade an answer (for open-ended questions).
     */
    public function gradeAnswer(int $answerId, float $points, ?string $feedback = null)
    {
        try {
            DB::beginTransaction();

            $answer = ExamAttemptAnswer::findOrFail($answerId);
            $question = $answer->question;

            // Ensure points don't exceed question points
            $points = min($points, $question->points);

            $answer->update([
                'points_awarded' => $points,
                'is_correct' => $points >= ($question->points / 2), // Consider correct if >= 50%
            ]);

            // Recalculate attempt score
            $this->attemptRepository->calculateScore($answer->exam_attempt_id);

            Log::info("Answer manually graded", [
                'answer_id' => $answerId,
                'points' => $points
            ]);

            DB::commit();
            return $answer;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error grading answer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get attempt details with results.
     */
    public function getAttemptResults(int $attemptId)
    {
        try {
            $attempt = $this->attemptRepository->getAttemptWithAnswers($attemptId);

            // Prepare detailed results
            $results = [
                'attempt' => $attempt,
                'exam' => $attempt->exam,
                'student' => $attempt->student,
                'answers' => $attempt->answers->map(function ($answer) {
                    return [
                        'question' => $answer->question,
                        'selected_option' => $answer->option,
                        'answer_text' => $answer->answer_text,
                        'is_correct' => $answer->is_correct,
                        'points_awarded' => $answer->points_awarded,
                        'correct_answers' => $answer->question->correctAnswers,
                    ];
                }),
                'summary' => [
                    'total_questions' => $attempt->exam->questions()->count(),
                    'answered_questions' => $attempt->answers->count(),
                    'correct_answers' => $attempt->answers->where('is_correct', true)->count(),
                    'total_score' => $attempt->score,
                    'percentage' => $attempt->percentage,
                    'passed' => $attempt->hasPassed(),
                    'time_spent' => $attempt->time_spent_seconds,
                ],
            ];

            return $results;
        } catch (\Exception $e) {
            Log::error('Error getting attempt results: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get student's attempts for an exam.
     */
    public function getStudentAttempts(int $studentId, int $examId)
    {
        try {
            return $this->attemptRepository->getStudentAttempts($studentId, $examId);
        } catch (\Exception $e) {
            Log::error('Error fetching student attempts: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get all attempts by student.
     */
    public function getStudentHistory(int $studentId)
    {
        try {
            return $this->attemptRepository->getAttemptsByStudent($studentId);
        } catch (\Exception $e) {
            Log::error('Error fetching student history: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get exam statistics.
     */
    public function getExamStatistics(int $examId)
    {
        try {
            return $this->attemptRepository->getExamStatistics($examId);
        } catch (\Exception $e) {
            Log::error('Error getting exam statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get student statistics.
     */
    public function getStudentStatistics(int $studentId)
    {
        try {
            return $this->attemptRepository->getStudentStatistics($studentId);
        } catch (\Exception $e) {
            Log::error('Error getting student statistics: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Abandon an exam attempt.
     */
    public function abandonExam(int $attemptId)
    {
        try {
            DB::beginTransaction();

            $attempt = $this->attemptRepository->findById($attemptId);
            $attempt->status = 'abandoned';
            $attempt->completed_at = now();
            $attempt->save();

            Log::info("Exam attempt abandoned", ['attempt_id' => $attemptId]);

            DB::commit();
            return $attempt;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error abandoning exam: ' . $e->getMessage());
            throw $e;
        }
    }
}

