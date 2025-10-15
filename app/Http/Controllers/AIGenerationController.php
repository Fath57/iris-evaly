<?php

namespace App\Http\Controllers;

use App\Services\AIGenerationService;
use App\Interfaces\AIPromptRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AIGenerationController extends Controller
{
    protected $aiService;
    protected $promptRepository;

    public function __construct(
        AIGenerationService $aiService,
        AIPromptRepositoryInterface $promptRepository
    ) {
        $this->aiService = $aiService;
        $this->promptRepository = $promptRepository;
    }

    /**
     * Generate questions using AI.
     */
    public function generate(Request $request)
    {
        try {
            $validated = $request->validate([
                'ai_provider' => 'required|in:chatgpt,gemini,perplexity',
                'subject_theme' => 'required|string',
                'difficulty_level' => 'required|in:easy,medium,hard',
                'questions_requested' => 'required|integer|min:1|max:20',
                'question_type' => 'nullable|in:multiple_choice,true_false,short_answer,essay',
                'prompt_id' => 'nullable|exists:ai_prompts,id',
                'custom_prompt' => 'nullable|string',
                'exam_id' => 'nullable|exists:exams,id',
            ]);

            $validated['user_id'] = Auth::id();

            $result = $this->aiService->generateQuestions($validated);

            return response()->json([
                'success' => true,
                'message' => 'Questions generated successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            Log::error('Error generating questions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error generating questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept generated questions.
     */
    public function acceptQuestions(Request $request, $historyId)
    {
        try {
            $validated = $request->validate([
                'question_ids' => 'required|array',
                'question_ids.*' => 'exists:questions,id',
                'exam_id' => 'nullable|exists:exams,id',
            ]);

            $this->aiService->acceptQuestions(
                $historyId,
                $validated['question_ids'],
                $validated['exam_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Questions accepted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting questions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error accepting questions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Rate generation quality.
     */
    public function rateGeneration(Request $request, $historyId)
    {
        try {
            $validated = $request->validate([
                'rating' => 'required|numeric|min:1|max:5',
                'feedback' => 'nullable|string',
            ]);

            $history = $this->aiService->rateGeneration(
                $historyId,
                $validated['rating'],
                $validated['feedback'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Generation rated successfully',
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('Error rating generation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error rating generation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get generation statistics.
     */
    public function statistics()
    {
        try {
            $statistics = $this->aiService->getStatistics(Auth::id());

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
     * Get all prompts.
     */
    public function prompts(Request $request)
    {
        try {
            $provider = $request->query('provider');
            
            if ($provider) {
                $prompts = $this->promptRepository->getPromptsByProvider($provider);
            } else {
                $prompts = $this->promptRepository->getActivePrompts();
            }

            return response()->json([
                'success' => true,
                'data' => $prompts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching prompts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching prompts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new prompt template.
     */
    public function createPrompt(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'prompt_template' => 'required|string',
                'ai_provider' => 'required|in:chatgpt,gemini,perplexity',
                'difficulty_level' => 'nullable|in:easy,medium,hard',
                'question_type' => 'nullable|in:multiple_choice,true_false,short_answer,essay',
                'is_default' => 'boolean',
            ]);

            $validated['created_by'] = Auth::id();
            $validated['is_active'] = true;

            $prompt = $this->promptRepository->create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Prompt created successfully',
                'data' => $prompt,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating prompt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating prompt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a prompt template.
     */
    public function updatePrompt(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'prompt_template' => 'sometimes|string',
                'ai_provider' => 'sometimes|in:chatgpt,gemini,perplexity',
                'difficulty_level' => 'nullable|in:easy,medium,hard',
                'question_type' => 'nullable|in:multiple_choice,true_false,short_answer,essay',
                'is_default' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $prompt = $this->promptRepository->update($id, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Prompt updated successfully',
                'data' => $prompt,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating prompt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating prompt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a prompt template.
     */
    public function deletePrompt($id)
    {
        try {
            $this->promptRepository->delete($id);

            return response()->json([
                'success' => true,
                'message' => 'Prompt deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting prompt: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error deleting prompt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get most used prompts.
     */
    public function mostUsedPrompts()
    {
        try {
            $prompts = $this->promptRepository->getMostUsedPrompts();

            return response()->json([
                'success' => true,
                'data' => $prompts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching most used prompts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching prompts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get highest rated prompts.
     */
    public function highestRatedPrompts()
    {
        try {
            $prompts = $this->promptRepository->getHighestRatedPrompts();

            return response()->json([
                'success' => true,
                'data' => $prompts,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching highest rated prompts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching prompts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

