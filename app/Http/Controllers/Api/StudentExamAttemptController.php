<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\QuestionOption;
use App\Models\Student;
use App\Services\ExamAttemptService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Student Exams",
 *     description="API Endpoints pour la soumission et les résultats des examens étudiants"
 * )
 */
class StudentExamAttemptController extends Controller
{
    public function __construct(
        private ExamAttemptService $examAttemptService
    ) {
    }

    /**
     * @OA\Post(
     *     path="/api/students/exams/{exam}/submit",
     *     summary="Soumettre un examen",
     *     description="Permet à un étudiant de soumettre ses réponses pour un examen donné",
     *     tags={"Student Exams"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID de l'examen",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"answers"},
     *             @OA\Property(
     *                 property="answers",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     required={"question_id"},
     *                     @OA\Property(property="question_id", type="integer", example=1),
     *                     @OA\Property(property="option_id", type="integer", nullable=true, example=10),
     *                     @OA\Property(property="answer_text", type="string", nullable=true, example="Réponse libre"),
     *                     @OA\Property(property="time_spent_seconds", type="integer", example=45)
     *                 )
     *             ),
     *             @OA\Property(property="time_spent_seconds", type="integer", nullable=true, example=350)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Examen soumis avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="attempt_id", type="integer", example=5),
     *                 @OA\Property(property="score", type="number", format="float", example=18.5),
     *                 @OA\Property(property="percentage", type="number", format="float", example=92.5),
     *                 @OA\Property(property="passed", type="boolean", example=true),
     *                 @OA\Property(property="summary", type="object"),
     *                 @OA\Property(property="answers", type="array", @OA\Items(type="object"))
     *             ),
     *             @OA\Property(property="message", type="string", example="Examen soumis avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation"
     *     )
     * )
     */
    public function submit(Request $request, Exam $exam): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();
        $exam->loadMissing(['questions.options', 'questions.correctAnswers']);

        $this->ensureStudentCanAccessExam($student, $exam);

        if (!$exam->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet examen n\'est pas disponible pour le moment.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer|distinct',
            'answers.*.option_id' => 'nullable|integer',
            'answers.*.answer_text' => 'nullable|string',
            'answers.*.time_spent_seconds' => 'nullable|integer|min:0',
            'time_spent_seconds' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        $questionIds = $exam->questions->pluck('id')->all();

        foreach ($validated['answers'] as $answer) {
            if (!in_array($answer['question_id'], $questionIds, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une des questions ne fait pas partie de cet examen.',
                ], 422);
            }

            if (array_key_exists('option_id', $answer) && $answer['option_id']) {
                $optionExists = QuestionOption::where('id', $answer['option_id'])
                    ->where('question_id', $answer['question_id'])
                    ->exists();

                if (!$optionExists) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Une des options sélectionnées n\'est pas valide pour cette question.',
                    ], 422);
                }
            }
        }

        try {
            $attempt = $this->examAttemptService->startExam($exam->id, $student->id);

            foreach ($validated['answers'] as $answerData) {
                $this->examAttemptService->submitAnswer($attempt->id, [
                    'question_id' => $answerData['question_id'],
                    'option_id' => $answerData['option_id'] ?? null,
                    'answer_text' => $answerData['answer_text'] ?? null,
                    'time_spent_seconds' => $answerData['time_spent_seconds'] ?? 0,
                ]);
            }

            $attempt = $this->examAttemptService->completeExam($attempt->id, [
                'time_spent_seconds' => $validated['time_spent_seconds'] ?? null,
            ]);

            $results = $this->examAttemptService->getAttemptResults($attempt->id);

            return response()->json([
                'success' => true,
                'data' => [
                    'attempt_id' => $attempt->id,
                    'score' => $attempt->score,
                    'percentage' => $attempt->percentage,
                    'passed' => $attempt->hasPassed(),
                    'summary' => $results['summary'],
                    'answers' => $results['answers'],
                ],
                'message' => 'Examen soumis avec succès.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission de l\'examen : ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/students/exams/{exam}/results",
     *     summary="Récupérer les résultats d'un examen",
     *     description="Récupère le détail de la dernière tentative complétée par l'étudiant pour un examen donné",
     *     tags={"Student Exams"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="exam",
     *         in="path",
     *         required=true,
     *         description="ID de l'examen",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Résultats récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Résultats récupérés avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun résultat trouvé"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé"
     *     )
     * )
     */
    public function results(Request $request, Exam $exam): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();
        $exam->loadMissing(['questions']);

        $this->ensureStudentCanAccessExam($student, $exam);

        $attempts = $this->examAttemptService
            ->getStudentAttempts($student->id, $exam->id)
            ->filter(fn ($attempt) => $attempt->status === 'completed')
            ->sortByDesc(fn ($attempt) => $attempt->completed_at ?? $attempt->created_at);

        $latestAttempt = $attempts->first();

        if (!$latestAttempt) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun résultat disponible pour cet examen.',
            ], 404);
        }

        $results = $this->examAttemptService->getAttemptResults($latestAttempt->id);

        return response()->json([
            'success' => true,
            'data' => [
                'attempt_id' => $latestAttempt->id,
                'score' => $latestAttempt->score,
                'percentage' => $latestAttempt->percentage,
                'passed' => $latestAttempt->hasPassed(),
                'summary' => $results['summary'],
                'answers' => $results['answers'],
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'total_points' => $exam->total_points,
                    'passing_score' => $exam->passing_score,
                ],
            ],
            'message' => 'Résultats récupérés avec succès.',
        ]);
    }

    private function ensureStudentCanAccessExam(Student $student, Exam $exam): void
    {
        $belongsToClass = $student->classes()
            ->where('class_id', $exam->class_id)
            ->exists();

        if (!$belongsToClass) {
            abort(403, 'Cet examen n\'est pas accessible pour cet étudiant.');
        }
    }
}


