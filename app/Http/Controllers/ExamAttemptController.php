<?php

namespace App\Http\Controllers;

use App\Services\ExamAttemptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExamAttemptController extends Controller
{
    protected $attemptService;

    public function __construct(ExamAttemptService $attemptService)
    {
        $this->attemptService = $attemptService;
    }

    /**
     * Start a new exam attempt.
     */
    public function start(Request $request, $examId)
    {
        try {
            $attempt = $this->attemptService->startExam($examId, Auth::id());

            return response()->json([
                'success' => true,
                'message' => 'Exam started successfully',
                'data' => $attempt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error starting exam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit an answer.
     */
    public function submitAnswer(Request $request, $attemptId)
    {
        try {
            $validated = $request->validate([
                'question_id' => 'required|exists:questions,id',
                'option_id' => 'nullable|exists:question_options,id',
                'answer_text' => 'nullable|string',
                'time_spent_seconds' => 'integer',
            ]);

            $answer = $this->attemptService->submitAnswer($attemptId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Answer submitted successfully',
                'data' => $answer,
            ]);
        } catch (\Exception $e) {
            Log::error('Error submitting answer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Complete exam attempt.
     */
    public function complete(Request $request, $attemptId)
    {
        try {
            $validated = $request->validate([
                'time_spent_seconds' => 'integer',
            ]);

            $attempt = $this->attemptService->completeExam($attemptId, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam completed successfully',
                'data' => $attempt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error completing exam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get attempt results.
     */
    public function results($attemptId)
    {
        try {
            $results = $this->attemptService->getAttemptResults($attemptId);

            return response()->json([
                'success' => true,
                'data' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching results: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Results not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Get student attempts for an exam.
     */
    public function studentAttempts($examId)
    {
        try {
            $attempts = $this->attemptService->getStudentAttempts(Auth::id(), $examId);

            return response()->json([
                'success' => true,
                'data' => $attempts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching attempts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching attempts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student exam history.
     */
    public function history()
    {
        try {
            $history = $this->attemptService->getStudentHistory(Auth::id());

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get exam statistics (for teachers/admins).
     */
    public function examStatistics($examId)
    {
        try {
            $statistics = $this->attemptService->getExamStatistics($examId);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get student statistics.
     */
    public function studentStatistics($studentId = null)
    {
        try {
            $studentId = $studentId ?? Auth::id();
            $statistics = $this->attemptService->getStudentStatistics($studentId);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Abandon exam attempt.
     */
    public function abandon($attemptId)
    {
        try {
            $attempt = $this->attemptService->abandonExam($attemptId);

            return response()->json([
                'success' => true,
                'message' => 'Exam abandoned',
                'data' => $attempt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error abandoning exam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error abandoning exam',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Manually grade an answer (for teachers).
     */
    public function gradeAnswer(Request $request, $answerId)
    {
        try {
            $validated = $request->validate([
                'points' => 'required|numeric|min:0',
                'feedback' => 'nullable|string',
            ]);

            $answer = $this->attemptService->gradeAnswer(
                $answerId,
                $validated['points'],
                $validated['feedback'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Answer graded successfully',
                'data' => $answer,
            ]);
        } catch (\Exception $e) {
            Log::error('Error grading answer: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error grading answer',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

