<?php

namespace App\Http\Controllers;

use App\Services\QuestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QuestionController extends Controller
{
    protected $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * Get questions by exam.
     */
    public function byExam($examId)
    {
        try {
            $questions = $this->questionService->getQuestionsByExam($examId);

            // Si la requête attend du JSON (API), retourner JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $questions,
                ]);
            }

            // Sinon, retourner la vue Inertia pour l'interface web
            return inertia('Admin/Exams/Questions', [
                'questions' => $questions,
                'examId' => $examId,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching questions: ' . $e->getMessage());

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error fetching questions',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Erreur lors du chargement des questions');
        }
    }

    /**
     * Get question details.
     */
    public function show($id)
    {
        try {
            $question = $this->questionService->getQuestionDetails($id);

            return response()->json([
                'success' => true,
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching question: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Question not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new question.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'exam_id' => 'required|exists:exams,id',
                'type' => 'required|in:multiple_choice,multiple_answers,true_false,short_answer,essay',
                'question_text' => 'required|string',
                'points' => 'required|integer|min:1',
                'order' => 'nullable|integer',
                'explanation' => 'nullable|string',
                'alt_text' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
                'correct_answer' => 'nullable|string',
                'options' => 'nullable|json',
            ]);

            // Gérer l'upload d'image
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('questions', 'public');
                $validated['image_path'] = $imagePath;
            }

            // Décoder les options si elles sont en JSON
            if (isset($validated['options'])) {
                $validated['options'] = json_decode($validated['options'], true);
            }

            $question = $this->questionService->createQuestion($validated);

            // Si requête Inertia, rediriger
            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Question créée avec succès');
            }

            return response()->json([
                'success' => true,
                'message' => 'Question created successfully',
                'data' => $question,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating question: ' . $e->getMessage());

            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('error', 'Erreur lors de la création de la question');
            }

            return response()->json([
                'success' => false,
                'message' => 'Error creating question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a question.
     */
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'type' => 'sometimes|in:multiple_choice,multiple_answers,true_false,short_answer,essay',
                'question_text' => 'sometimes|string',
                'points' => 'sometimes|integer|min:1',
                'order' => 'nullable|integer',
                'explanation' => 'nullable|string',
                'alt_text' => 'nullable|string',
                'image' => 'nullable|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
                'correct_answer' => 'nullable|string',
                'options' => 'nullable|json',
            ]);

            // Gérer l'upload d'image
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('questions', 'public');
                $validated['image_path'] = $imagePath;
            }

            // Décoder les options si elles sont en JSON
            if (isset($validated['options'])) {
                $validated['options'] = json_decode($validated['options'], true);
            }

            $question = $this->questionService->updateQuestion($id, $validated);

            // Si requête Inertia, rediriger
            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('success', 'Question mise à jour avec succès');
            }

            return response()->json([
                'success' => true,
                'message' => 'Question updated successfully',
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating question: ' . $e->getMessage());

            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('error', 'Erreur lors de la mise à jour de la question');
            }

            return response()->json([
                'success' => false,
                'message' => 'Error updating question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a question.
     */
    public function destroy($id)
    {
        try {
            $this->questionService->deleteQuestion($id);

            return response()->json([
                'success' => true,
                'message' => 'Question deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting question: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Duplicate a question.
     */
    public function duplicate($id)
    {
        try {
            $question = $this->questionService->duplicateQuestion($id);

            return response()->json([
                'success' => true,
                'message' => 'Question duplicated successfully',
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error duplicating question: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error duplicating question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add question to bank.
     */
    public function addToBank($id)
    {
        try {
            $question = $this->questionService->addToBank($id);

            return response()->json([
                'success' => true,
                'message' => 'Question added to bank successfully',
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding question to bank: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding question to bank',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove question from bank.
     */
    public function removeFromBank($id)
    {
        try {
            $question = $this->questionService->removeFromBank($id);

            return response()->json([
                'success' => true,
                'message' => 'Question removed from bank successfully',
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing question from bank: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error removing question from bank',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import question to exam.
     */
    public function importToExam(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'exam_id' => 'required|exists:exams,id',
            ]);

            $question = $this->questionService->importToExam($id, $validated['exam_id']);

            return response()->json([
                'success' => true,
                'message' => 'Question imported to exam successfully',
                'data' => $question,
            ]);
        } catch (\Exception $e) {
            Log::error('Error importing question: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error importing question',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get questions in bank.
     */
    public function bank(Request $request)
    {
        try {
            $filters = $request->only(['category', 'difficulty', 'type']);
            $questions = $this->questionService->getQuestionsInBank($filters);

            return response()->json([
                'success' => true,
                'data' => $questions,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching questions bank: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload image for question.
     */
    public function uploadImage(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'file' => 'required|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
                'image_type' => 'required|in:question,option,diagram,schema',
                'alt_text' => 'nullable|string',
                'option_id' => 'nullable|exists:question_options,id',
            ]);

            $validated['file'] = $request->file('file');
            $image = $this->questionService->addImage($id, $validated, $validated['option_id'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'data' => $image,
            ]);
        } catch (\Exception $e) {
            Log::error('Error uploading image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error uploading image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete image.
     */
    public function deleteImage($questionId, $imageId)
    {
        try {
            $this->questionService->removeImage($imageId);

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting image',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reorder questions in an exam.
     */
    public function reorder(Request $request, $examId)
    {
        try {
            $validated = $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'exists:questions,id',
            ]);

            $questions = $this->questionService->reorderQuestions($examId, $validated['question_ids']);

            return response()->json([
                'success' => true,
                'message' => 'Questions reordered successfully',
                'data' => $questions,
            ]);
        } catch (\Exception $e) {
            Log::error('Error reordering questions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error reordering questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

