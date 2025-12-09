<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Student Authentication",
 *     description="API Endpoints pour l'authentification des étudiants"
 * )
 */
class StudentAuthController extends Controller
{
    public function __construct(
        private StudentAuthService $studentAuthService,
        private \App\Services\ExamService $examService
    ) {}

    /**
     * @OA\Post(
     *     path="/api/students/login",
     *     summary="Connexion étudiant",
     *     description="Authentifie un étudiant avec email et mot de passe",
     *     tags={"Student Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="etudiant@example.com"),
     *             @OA\Property(property="password", type="string", example="motdepasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Connexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="student", type="object"),
     *                 @OA\Property(property="token", type="string")
     *             ),
     *             @OA\Property(property="message", type="string", example="Connexion réussie.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Erreur d'authentification",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="requires_password_setup", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        $result = $this->studentAuthService->login($request->only(['email', 'password']));

        if (!$result['success']) {
            $statusCode = isset($result['errors']) ? 422 : 401;
            return response()->json($result, $statusCode);
        }

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/students/setup-password",
     *     summary="Définir le mot de passe initial",
     *     description="Permet à un étudiant de définir son mot de passe pour la première fois en utilisant son email",
     *     tags={"Student Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password", "password_confirmation"},
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="motdepasse123"),
     *             @OA\Property(property="password_confirmation", type="string", example="motdepasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe défini avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="student", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="first_name", type="string", example="Jean"),
     *                     @OA\Property(property="last_name", type="string", example="Dupont"),
     *                     @OA\Property(property="email", type="string", example="jean.dupont@example.com"),
     *                     @OA\Property(property="student_number", type="string", example="STU202500001")
     *                 ),
     *                 @OA\Property(property="token", type="string", example="1|abcd...xyz")
     *             ),
     *             @OA\Property(property="message", type="string", example="Mot de passe défini avec succès. Vous êtes maintenant connecté.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreurs de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function setupPassword(Request $request): JsonResponse
    {
        $result = $this->studentAuthService->setupPassword(
            $request->input('email'),
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * @OA\Post(
     *     path="/api/students/change-password",
     *     summary="Changer le mot de passe",
     *     description="Permet à un étudiant connecté de changer son mot de passe",
     *     tags={"Student Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password", "password", "password_confirmation"},
     *             @OA\Property(property="current_password", type="string", example="ancienmdp123"),
     *             @OA\Property(property="password", type="string", minLength=8, example="nouveaumdp123"),
     *             @OA\Property(property="password_confirmation", type="string", example="nouveaumdp123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Mot de passe modifié avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function changePassword(Request $request): JsonResponse
    {
        $student = $request->user();

        $result = $this->studentAuthService->changePassword(
            $student->id,
            $request->input('current_password'),
            $request->input('password'),
            $request->input('password_confirmation')
        );

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/api/students/profile",
     *     summary="Récupérer le profil de l'étudiant connecté",
     *     description="Récupère les informations du profil de l'étudiant authentifié",
     *     tags={"Student Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Profil récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function getProfile(Request $request): JsonResponse
    {
        $student = $request->user();

        $result = $this->studentAuthService->getProfile($student->id);

        return response()->json($result);
    }

    /**
     * @OA\Put(
     *     path="/api/students/profile",
     *     summary="Mettre à jour le profil",
     *     description="Met à jour les informations du profil de l'étudiant connecté",
     *     tags={"Student Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="phone", type="string", example="+33123456789"),
     *             @OA\Property(property="address", type="string", example="123 Rue de la Paix, Paris"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profil mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $student = $request->user();

        $result = $this->studentAuthService->updateProfile($student->id, $request->all());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json($result);
    }

    /**
     * @OA\Get(
     *     path="/api/students/exams",
     *     summary="Récupérer les examens de l'étudiant",
     *     description="Récupère les examens de l'étudiant connecté, catégorisés par statut (à venir, en cours, passés)",
     *     tags={"Student Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des examens récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="upcoming", type="array", @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Examen de Mathématiques"),
     *                     @OA\Property(property="description", type="string", example="Examen sur les équations"),
     *                     @OA\Property(property="subject", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Mathématiques")
     *                     ),
     *                     @OA\Property(property="class", type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Terminale S1")
     *                     ),
     *                     @OA\Property(property="duration", type="integer", example=60),
     *                     @OA\Property(property="total_points", type="number", format="float", example=20),
     *                     @OA\Property(property="passing_score", type="number", format="float", example=10),
     *                     @OA\Property(property="start_date", type="string", format="date-time", example="2025-11-01T10:00:00Z"),
     *                     @OA\Property(property="end_date", type="string", format="date-time", example="2025-11-01T12:00:00Z"),
     *                     @OA\Property(property="questions_count", type="integer", example=20),
     *                     @OA\Property(property="max_attempts", type="integer", example=2),
     *                     @OA\Property(property="attempt_count", type="integer", example=0),
     *                     @OA\Property(property="can_attempt", type="boolean", example=true),
     *                     @OA\Property(property="best_score", type="number", format="float", example=null),
     *                     @OA\Property(property="last_attempt", type="object", nullable=true)
     *                 )),
     *                 @OA\Property(property="ongoing", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="past", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="completed", type="array", description="Examens complétés par l'étudiant", @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="title", type="string"),
     *                     @OA\Property(property="best_attempt", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="score", type="number"),
     *                         @OA\Property(property="percentage", type="number"),
     *                         @OA\Property(property="passed", type="boolean"),
     *                         @OA\Property(property="completed_at", type="string", format="date-time")
     *                     )
     *                 ))
     *             ),
     *             @OA\Property(property="message", type="string", example="Examens récupérés avec succès.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getExams(Request $request): JsonResponse
    {
        $student = $request->user();

        try {
            $exams = $this->examService->getStudentExams($student->id);

            return response()->json([
                'success' => true,
                'data' => $exams,
                'message' => 'Examens récupérés avec succès.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des examens: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/students/logout",
     *     summary="Déconnexion étudiant",
     *     description="Déconnecte l'étudiant et révoque son token",
     *     tags={"Student Authentication"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Déconnexion réussie",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Déconnexion réussie.")
     *         )
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $student = $request->user();

        $result = $this->studentAuthService->logout($student);

        return response()->json($result);
    }
}
