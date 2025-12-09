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
     * @OA\Get(
     *     path="/api/students/exams/{exam}/questions",
     *     summary="Récupérer les questions d'un examen",
     *     description="Récupère toutes les questions d'un examen avec leurs options pour permettre à l'étudiant de passer l'examen",
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
     *         description="Questions récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="exam", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Examen de Mathématiques"),
     *                     @OA\Property(property="description", type="string"),
     *                     @OA\Property(property="instructions", type="string"),
     *                     @OA\Property(property="duration_minutes", type="integer", example=60),
     *                     @OA\Property(property="total_points", type="integer", example=20),
     *                     @OA\Property(property="passing_score", type="integer", example=10),
     *                     @OA\Property(property="shuffle_questions", type="boolean"),
     *                     @OA\Property(property="shuffle_options", type="boolean"),
     *                     @OA\Property(property="allow_navigation", type="boolean"),
     *                     @OA\Property(property="questions_per_page", type="integer")
     *                 ),
     *                 @OA\Property(property="questions", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="type", type="string", example="multiple_choice"),
     *                         @OA\Property(property="question_text", type="string"),
     *                         @OA\Property(property="points", type="integer", example=4),
     *                         @OA\Property(property="order", type="integer", example=1),
     *                         @OA\Property(property="options", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer"),
     *                                 @OA\Property(property="option_text", type="string"),
     *                                 @OA\Property(property="option_key", type="string")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="attempt_info", type="object",
     *                     @OA\Property(property="attempt_count", type="integer", example=0),
     *                     @OA\Property(property="max_attempts", type="integer", example=2),
     *                     @OA\Property(property="can_attempt", type="boolean", example=true)
     *                 )
     *             ),
     *             @OA\Property(property="message", type="string", example="Questions récupérées avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Accès refusé ou examen non disponible"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Examen non trouvé"
     *     )
     * )
     */
    public function questions(Request $request, Exam $exam): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $this->ensureStudentCanAccessExam($student, $exam);

        if (!$exam->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Cet examen n\'est pas disponible pour le moment.',
            ], 403);
        }

        // Vérifier si l'étudiant peut encore tenter l'examen
        $attemptCount = $exam->attempts()
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        if ($attemptCount >= $exam->max_attempts) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez atteint le nombre maximum de tentatives pour cet examen.',
            ], 403);
        }

        // Charger les questions avec leurs options et images
        $exam->load([
            'questions.options.images',
            'questions.images',
            'subject'
        ]);

        $questions = $exam->questions->sortBy('order')->map(function ($question) use ($exam) {
            $options = $question->options->sortBy('order')->map(function ($option) {
                $optionData = [
                    'id' => $option->id,
                    'option_text' => $option->option_text,
                    'option_key' => $option->option_key,
                ];

                // Ajouter les images de l'option si présentes
                if ($option->images->isNotEmpty()) {
                    $optionData['images'] = $option->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => $image->image_url,
                            'alt_text' => $image->alt_text,
                            'width' => $image->width,
                            'height' => $image->height,
                        ];
                    })->values();
                }

                return $optionData;
            });

            // Mélanger les options si configuré
            if ($exam->shuffle_options) {
                $options = $options->shuffle();
            }

            $questionData = [
                'id' => $question->id,
                'type' => $question->type,
                'question_text' => $question->question_text,
                'points' => $question->points,
                'order' => $question->order,
                'options' => $options->values(),
            ];

            // Ajouter les images de la question si présentes
            if ($question->images->isNotEmpty()) {
                $questionData['images'] = $question->images->map(function ($image) {
                    return [
                        'id' => $image->id,
                        'url' => $image->image_url,
                        'alt_text' => $image->alt_text,
                        'image_type' => $image->image_type,
                        'width' => $image->width,
                        'height' => $image->height,
                    ];
                })->values();
            }

            return $questionData;
        });

        // Mélanger les questions si configuré
        if ($exam->shuffle_questions) {
            $questions = $questions->shuffle()->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exam' => [
                    'id' => $exam->id,
                    'title' => $exam->title,
                    'description' => $exam->description,
                    'instructions' => $exam->instructions,
                    'duration_minutes' => $exam->duration_minutes,
                    'total_points' => $exam->total_points,
                    'passing_score' => $exam->passing_score,
                    'shuffle_questions' => $exam->shuffle_questions,
                    'shuffle_options' => $exam->shuffle_options,
                    'allow_navigation' => $exam->allow_navigation,
                    'questions_per_page' => $exam->questions_per_page,
                    'subject' => [
                        'id' => $exam->subject->id,
                        'name' => $exam->subject->name,
                    ],
                ],
                'questions' => $questions,
                'attempt_info' => [
                    'attempt_count' => $attemptCount,
                    'max_attempts' => $exam->max_attempts,
                    'can_attempt' => $attemptCount < $exam->max_attempts,
                ],
            ],
            'message' => 'Questions récupérées avec succès.',
        ]);
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


