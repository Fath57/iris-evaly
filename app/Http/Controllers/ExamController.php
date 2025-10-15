<?php

namespace App\Http\Controllers;

use App\Services\ExamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use App\Models\Subject;
use App\Models\ClassModel;

class ExamController extends Controller
{
    protected $examService;

    public function __construct(ExamService $examService)
    {
        $this->examService = $examService;
    }

    /**
     * Display a listing of exams.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            
            if ($user->hasRole('teacher')) {
                $exams = $this->examService->getExamsByTeacher($user->id);
            } elseif ($user->hasRole('student')) {
                // Get available exams for student's classes
                $classIds = $user->classes()->pluck('class_id');
                $exams = collect();
                foreach ($classIds as $classId) {
                    $exams = $exams->merge(
                        $this->examService->getAvailableExamsForStudent($user->id, $classId)
                    );
                }
            } else {
                // Admin can see all exams
                $exams = $this->examService->getExamsByTeacher($user->id);
            }

            $subjects = Subject::all();
            $classes = ClassModel::all();

            return Inertia::render('Admin/Exams/Index', [
                'exams' => $exams,
                'subjects' => $subjects,
                'classes' => $classes,
                'userRole' => $user->roles->first()?->name ?? 'user',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching exams: ' . $e->getMessage());
            return back()->with('error', 'Error fetching exams: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new exam.
     */
    public function create()
    {
        try {
            $subjects = Subject::all();
            $classes = ClassModel::all();

            return Inertia::render('Admin/Exams/Create', [
                'subjects' => $subjects,
                'classes' => $classes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading create form: ' . $e->getMessage());
            return back()->with('error', 'Error loading form');
        }
    }

    /**
     * Display the specified exam.
     */
    public function show($id)
    {
        try {
            $exam = $this->examService->getExamDetails($id);

            return Inertia::render('Admin/Exams/Show', [
                'exam' => $exam,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching exam: ' . $e->getMessage());
            return back()->with('error', 'Exam not found');
        }
    }

    /**
     * Show the form for editing the specified exam.
     */
    public function edit($id)
    {
        try {
            $exam = $this->examService->getExamDetails($id);
            $subjects = Subject::all();
            $classes = ClassModel::all();

            return Inertia::render('Admin/Exams/Edit', [
                'exam' => $exam,
                'subjects' => $subjects,
                'classes' => $classes,
            ]);
        } catch (\Exception $e) {
            Log::error('Error loading edit form: ' . $e->getMessage());
            return back()->with('error', 'Error loading exam');
        }
    }

    /**
     * Create a new exam.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'subject_id' => 'required|exists:subjects,id',
                'class_id' => 'required|exists:classes,id',
                'duration_minutes' => 'required|integer|min:1',
                'passing_score' => 'required|integer|min:0|max:100',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after:start_date',
                'shuffle_questions' => 'boolean',
                'shuffle_options' => 'boolean',
                'show_results_immediately' => 'boolean',
                'allow_review' => 'boolean',
                'show_correct_answers' => 'boolean',
                'require_all_questions' => 'boolean',
                'questions_per_page' => 'integer|min:1',
                'allow_navigation' => 'boolean',
                'max_attempts' => 'integer|min:1',
                'success_message' => 'nullable|string',
                'failure_message' => 'nullable|string',
            ]);

            $validated['created_by'] = Auth::id();
            $validated['status'] = 'draft';

            $exam = $this->examService->createExam($validated);

            return redirect()->route('admin.exams.show', $exam->id)
                ->with('success', 'Exam created successfully');
        } catch (\Exception $e) {
            Log::error('Error creating exam: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erreur lors de la création de l\'examen: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Update an exam.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'instructions' => 'nullable|string',
                'subject_id' => 'sometimes|exists:subjects,id',
                'class_id' => 'sometimes|exists:classes,id',
                'duration_minutes' => 'sometimes|integer|min:1',
                'passing_score' => 'sometimes|integer|min:0|max:100',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date',
                'shuffle_questions' => 'boolean',
                'shuffle_options' => 'boolean',
                'show_results_immediately' => 'boolean',
                'allow_review' => 'boolean',
                'show_correct_answers' => 'boolean',
                'require_all_questions' => 'boolean',
                'questions_per_page' => 'integer|min:1',
                'allow_navigation' => 'boolean',
                'max_attempts' => 'integer|min:1',
                'success_message' => 'nullable|string',
                'failure_message' => 'nullable|string',
            ]);

            $exam = $this->examService->updateExam($id, $validated);

            return redirect()->route('admin.exams.show', $id)
                ->with('success', 'Exam updated successfully');
        } catch (\Exception $e) {
            Log::error('Error updating exam: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erreur lors de la mise à jour de l\'examen: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Delete an exam.
     */
    public function destroy($id)
    {
        try {
            $this->examService->deleteExam($id);

            return redirect()->route('admin.exams.index')
                ->with('success', 'Exam deleted successfully');
        } catch (\Exception $e) {
            Log::error('Error deleting exam: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Erreur lors de la suppression de l\'examen: ' . $e->getMessage()]);
        }
    }

    /**
     * Publish an exam.
     */
    public function publish($id)
    {
        try {
            $exam = $this->examService->publishExam($id);

            return response()->json([
                'success' => true,
                'message' => 'Exam published successfully',
                'data' => $exam,
            ]);
        } catch (\Exception $e) {
            Log::error('Error publishing exam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Archive an exam.
     */
    public function archive($id)
    {
        try {
            $exam = $this->examService->archiveExam($id);

            return response()->json([
                'success' => true,
                'message' => 'Exam archived successfully',
                'data' => $exam,
            ]);
        } catch (\Exception $e) {
            Log::error('Error archiving exam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error archiving exam',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate an exam.
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
            ]);

            $exam = $this->examService->duplicateExam($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Exam duplicated successfully',
                'data' => $exam,
            ]);
        } catch (\Exception $e) {
            Log::error('Error duplicating exam: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error duplicating exam',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get exam statistics.
     */
    public function statistics($id)
    {
        try {
            $statistics = $this->examService->getExamStatistics($id);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching exam statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get exams by class.
     */
    public function byClass($classId)
    {
        try {
            $exams = $this->examService->getExamsByClass($classId);

            return response()->json([
                'success' => true,
                'data' => $exams,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching exams by class: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching exams',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get exams by subject.
     */
    public function bySubject($subjectId)
    {
        try {
            $exams = $this->examService->getExamsBySubject($subjectId);

            return response()->json([
                'success' => true,
                'data' => $exams,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching exams by subject: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching exams',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

