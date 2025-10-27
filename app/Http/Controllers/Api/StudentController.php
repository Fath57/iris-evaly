<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @OA\Tag(
 *     name="Students",
 *     description="API Endpoints pour la gestion des étudiants"
 * )
 */
class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService
    ) {}

    /**
     * @OA\Get(
     *     path="/api/students",
     *     summary="Récupérer la liste des étudiants",
     *     description="Récupère la liste paginée des étudiants avec filtres optionnels",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Numéro de page",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Nombre d'éléments par page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Terme de recherche (nom, prénom, email, numéro étudiant)",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="class_id",
     *         in="query",
     *         description="ID de la classe pour filtrer",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         description="Statut actif (true/false)",
     *         required=false,
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des étudiants récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'class_id', 'is_active']);
        $perPage = $request->get('per_page', 15);

        $students = $this->studentService->getStudents($filters, $perPage);

        return response()->json([
            'success' => true,
            'data' => $students,
            'message' => 'Liste des étudiants récupérée avec succès.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/students",
     *     summary="Créer un nouvel étudiant",
     *     description="Crée un nouvel étudiant avec les données fournies",
     *     tags={"Students"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"first_name", "last_name", "email"},
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *             @OA\Property(property="student_number", type="string", example="STU202400001"),
     *             @OA\Property(property="phone", type="string", example="+33123456789"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
     *             @OA\Property(property="address", type="string", example="123 Rue de la Paix, Paris"),
     *             @OA\Property(property="parent_contact", type="string", example="parent@example.com"),
     *             @OA\Property(property="password", type="string", minLength=8, example="motdepasse123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Étudiant créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
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
    public function store(Request $request): JsonResponse
    {
        $result = $this->studentService->createStudent($request->all());

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'message' => 'Étudiant créé avec succès.'
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/students/{id}",
     *     summary="Récupérer un étudiant par ID",
     *     description="Récupère les détails d'un étudiant spécifique",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'étudiant",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Détails de l'étudiant récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Étudiant non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(int $id): JsonResponse
    {
        $student = $this->studentService->getStudent($id);

        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $student,
            'message' => 'Détails de l\'étudiant récupérés avec succès.'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/students/{id}",
     *     summary="Mettre à jour un étudiant",
     *     description="Met à jour les informations d'un étudiant existant",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'étudiant",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="first_name", type="string", example="Jean"),
     *             @OA\Property(property="last_name", type="string", example="Dupont"),
     *             @OA\Property(property="email", type="string", format="email", example="jean.dupont@example.com"),
     *             @OA\Property(property="phone", type="string", example="+33123456789"),
     *             @OA\Property(property="date_of_birth", type="string", format="date", example="2000-01-01"),
     *             @OA\Property(property="address", type="string", example="123 Rue de la Paix, Paris"),
     *             @OA\Property(property="parent_contact", type="string", example="parent@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Étudiant mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $result = $this->studentService->updateStudent($id, $request->all());

        if (!$result['success']) {
            return response()->json($result, $result['data'] ? 422 : 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'message' => 'Étudiant mis à jour avec succès.'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/students/import/{classId}",
     *     summary="Importer des étudiants depuis un fichier Excel",
     *     description="Importe une liste d'étudiants depuis un fichier Excel et les assigne à une classe",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="classId",
     *         in="path",
     *         description="ID de la classe",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary",
     *                     description="Fichier Excel contenant les données des étudiants"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Import réalisé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function import(Request $request, int $classId): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new StudentsImport($classId);
            Excel::import($import, $request->file('file'));

            $studentsData = $import->getStudents();

            if (empty($studentsData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune donnée valide trouvée dans le fichier.'
                ], 422);
            }

            $result = $this->studentService->importStudents($studentsData, $classId);

            if (!$result['success']) {
                return response()->json($result, 422);
            }

            return response()->json([
                'success' => true,
                'data' => $result['data'],
                'message' => 'Import réalisé avec succès.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'import: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/students/{id}/assign-class/{classId}",
     *     summary="Assigner un étudiant à une classe",
     *     description="Assigne un étudiant existant à une classe",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de l'étudiant",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="classId",
     *         in="path",
     *         description="ID de la classe",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Étudiant assigné à la classe avec succès"
     *     )
     * )
     */
    public function assignToClass(int $id, int $classId): JsonResponse
    {
        $result = $this->studentService->assignToClass($id, $classId);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Erreur lors de l\'assignation de l\'étudiant à la classe.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? null,
            'message' => 'Étudiant assigné à la classe avec succès.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/classes/{classId}/students",
     *     summary="Récupérer les étudiants d'une classe",
     *     description="Récupère tous les étudiants assignés à une classe spécifique",
     *     tags={"Students"},
     *     @OA\Parameter(
     *         name="classId",
     *         in="path",
     *         description="ID de la classe",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des étudiants de la classe récupérée avec succès"
     *     )
     * )
     */
    public function getByClass(int $classId): JsonResponse
    {
        $students = $this->studentService->getStudentsByClass($classId);

        return response()->json([
            'success' => true,
            'data' => $students,
            'message' => 'Liste des étudiants de la classe récupérée avec succès.'
        ]);
    }
}
