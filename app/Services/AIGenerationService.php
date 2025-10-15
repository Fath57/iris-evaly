<?php

namespace App\Services;

use App\Interfaces\AIPromptRepositoryInterface;
use App\Interfaces\AIGenerationHistoryRepositoryInterface;
use App\Models\AIGenerationHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AIGenerationService
{
    protected $promptRepository;
    protected $historyRepository;
    protected $questionService;

    public function __construct(
        AIPromptRepositoryInterface $promptRepository,
        AIGenerationHistoryRepositoryInterface $historyRepository,
        QuestionService $questionService
    ) {
        $this->promptRepository = $promptRepository;
        $this->historyRepository = $historyRepository;
        $this->questionService = $questionService;
    }

    /**
     * Generate questions using AI.
     */
    public function generateQuestions(array $data)
    {
        try {
            DB::beginTransaction();

            // Create generation history record
            $history = $this->historyRepository->create([
                'user_id' => $data['user_id'],
                'prompt_id' => $data['prompt_id'] ?? null,
                'ai_provider' => $data['ai_provider'],
                'subject_theme' => $data['subject_theme'],
                'difficulty_level' => $data['difficulty_level'],
                'questions_requested' => $data['questions_requested'],
                'questions_generated' => 0,
                'custom_prompt' => $data['custom_prompt'] ?? null,
                'status' => 'pending',
            ]);

            // Get prompt template
            $prompt = $this->buildPrompt($data);

            // Generate questions based on provider
            $generatedData = match ($data['ai_provider']) {
                'chatgpt' => $this->generateWithChatGPT($prompt, $data),
                'gemini' => $this->generateWithGemini($prompt, $data),
                'perplexity' => $this->generateWithPerplexity($prompt, $data),
                default => throw new \Exception('Unsupported AI provider'),
            };

            // Parse and create questions
            $questions = $this->parseGeneratedQuestions($generatedData['questions'], $data);

            // Update history
            $history = $this->historyRepository->update($history->id, [
                'questions_generated' => count($questions),
                'tokens_used' => $generatedData['tokens_used'] ?? null,
                'cost' => $generatedData['cost'] ?? null,
                'status' => 'completed',
            ]);

            // Increment prompt usage
            if (isset($data['prompt_id'])) {
                $this->promptRepository->incrementUsage($data['prompt_id']);
            }

            Log::info("AI questions generated", [
                'history_id' => $history->id,
                'provider' => $data['ai_provider'],
                'count' => count($questions)
            ]);

            DB::commit();

            return [
                'history' => $history,
                'questions' => $questions,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Update history as failed
            if (isset($history)) {
                $this->historyRepository->update($history->id, [
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);
            }

            Log::error('Error generating questions with AI: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate questions using ChatGPT.
     */
    protected function generateWithChatGPT(string $prompt, array $data)
    {
        try {
            $apiKey = config('services.openai.api_key');
            
            if (!$apiKey) {
                throw new \Exception('OpenAI API key not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert educator creating high-quality exam questions. Return questions in valid JSON format.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
                'max_tokens' => 2000,
            ]);

            if (!$response->successful()) {
                throw new \Exception('ChatGPT API error: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            // Extract JSON from markdown code blocks if present
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }

            $questions = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from ChatGPT');
            }

            return [
                'questions' => $questions['questions'] ?? $questions,
                'tokens_used' => $result['usage']['total_tokens'] ?? null,
                'cost' => $this->calculateCost('chatgpt', $result['usage']['total_tokens'] ?? 0),
            ];
        } catch (\Exception $e) {
            Log::error('ChatGPT generation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate questions using Gemini.
     */
    protected function generateWithGemini(string $prompt, array $data)
    {
        try {
            $apiKey = config('services.gemini.api_key');
            
            if (!$apiKey) {
                throw new \Exception('Gemini API key not configured');
            }

            $response = Http::timeout(60)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $apiKey,
                [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                            ],
                        ],
                    ],
                ]
            );

            if (!$response->successful()) {
                throw new \Exception('Gemini API error: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Extract JSON from markdown code blocks if present
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }

            $questions = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Gemini');
            }

            return [
                'questions' => $questions['questions'] ?? $questions,
                'tokens_used' => null, // Gemini doesn't provide token count in the same way
                'cost' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Gemini generation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate questions using Perplexity.
     */
    protected function generateWithPerplexity(string $prompt, array $data)
    {
        try {
            $apiKey = config('services.perplexity.api_key');
            
            if (!$apiKey) {
                throw new \Exception('Perplexity API key not configured');
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(60)->post('https://api.perplexity.ai/chat/completions', [
                'model' => 'llama-3.1-sonar-large-128k-online',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are an expert educator creating high-quality exam questions. Return questions in valid JSON format.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ]);

            if (!$response->successful()) {
                throw new \Exception('Perplexity API error: ' . $response->body());
            }

            $result = $response->json();
            $content = $result['choices'][0]['message']['content'] ?? '';
            
            // Extract JSON from markdown code blocks if present
            if (preg_match('/```json\s*(.*?)\s*```/s', $content, $matches)) {
                $content = $matches[1];
            }

            $questions = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from Perplexity');
            }

            return [
                'questions' => $questions['questions'] ?? $questions,
                'tokens_used' => null,
                'cost' => null,
            ];
        } catch (\Exception $e) {
            Log::error('Perplexity generation error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build prompt from template and data.
     */
    protected function buildPrompt(array $data): string
    {
        $prompt = '';

        if (isset($data['prompt_id'])) {
            $promptTemplate = $this->promptRepository->findById($data['prompt_id']);
            $prompt = $promptTemplate->prompt_template;
        } elseif (isset($data['custom_prompt'])) {
            $prompt = $data['custom_prompt'];
        } else {
            $prompt = $this->getDefaultPrompt($data['question_type'] ?? 'multiple_choice');
        }

        // Replace variables in prompt
        $prompt = str_replace(
            ['{subject_theme}', '{difficulty_level}', '{questions_count}', '{question_type}'],
            [$data['subject_theme'], $data['difficulty_level'], $data['questions_requested'], $data['question_type'] ?? 'multiple_choice'],
            $prompt
        );

        return $prompt;
    }

    /**
     * Get default prompt template.
     */
    protected function getDefaultPrompt(string $questionType): string
    {
        return <<<PROMPT
Create {questions_count} {question_type} questions about {subject_theme} at {difficulty_level} difficulty level.

Return the questions in the following JSON format:
{
  "questions": [
    {
      "question_text": "Question text here",
      "type": "multiple_choice",
      "difficulty_level": "{difficulty_level}",
      "points": 1,
      "options": [
        {"option_text": "Option A", "option_key": "A", "is_correct": false},
        {"option_text": "Option B", "option_key": "B", "is_correct": true},
        {"option_text": "Option C", "option_key": "C", "is_correct": false},
        {"option_text": "Option D", "option_key": "D", "is_correct": false}
      ],
      "explanation": "Explanation of the correct answer",
      "category": "Category or topic"
    }
  ]
}

Make sure each question is clear, accurate, and appropriate for the difficulty level.
PROMPT;
    }

    /**
     * Parse generated questions and create them.
     */
    protected function parseGeneratedQuestions(array $generatedQuestions, array $data): array
    {
        $questions = [];

        foreach ($generatedQuestions as $questionData) {
            // Add is_in_bank flag
            $questionData['is_in_bank'] = true;
            $questionData['exam_id'] = $data['exam_id'] ?? null;

            // Create the question
            $question = $this->questionService->createQuestion($questionData);
            $questions[] = $question;
        }

        return $questions;
    }

    /**
     * Accept generated questions and add them to exam or bank.
     */
    public function acceptQuestions(int $historyId, array $questionIds, ?int $examId = null)
    {
        try {
            DB::beginTransaction();

            foreach ($questionIds as $questionId) {
                if ($examId) {
                    // Import to exam
                    $this->questionService->importToExam($questionId, $examId);
                } else {
                    // Just add to bank
                    $this->questionService->addToBank($questionId);
                }
            }

            // Update history
            $this->historyRepository->update($historyId, [
                'questions_accepted' => count($questionIds),
            ]);

            Log::info("AI generated questions accepted", [
                'history_id' => $historyId,
                'count' => count($questionIds)
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error accepting AI questions: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Rate generation quality.
     */
    public function rateGeneration(int $historyId, float $rating, ?string $feedback = null)
    {
        try {
            $history = $this->historyRepository->update($historyId, [
                'quality_rating' => $rating,
                'feedback' => $feedback,
            ]);

            // Update prompt quality rating if prompt was used
            if ($history->prompt_id) {
                $this->promptRepository->updateQualityRating($history->prompt_id, $rating);
            }

            Log::info("Generation rated", [
                'history_id' => $historyId,
                'rating' => $rating
            ]);

            return $history;
        } catch (\Exception $e) {
            Log::error('Error rating generation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate API cost.
     */
    protected function calculateCost(string $provider, int $tokens): float
    {
        // Approximate costs (adjust based on actual pricing)
        $costs = [
            'chatgpt' => 0.002 / 1000, // $0.002 per 1K tokens
            'gemini' => 0.001 / 1000,
            'perplexity' => 0.001 / 1000,
        ];

        return ($costs[$provider] ?? 0) * $tokens;
    }

    /**
     * Get generation statistics.
     */
    public function getStatistics(int $userId)
    {
        try {
            return $this->historyRepository->getGenerationStatistics($userId);
        } catch (\Exception $e) {
            Log::error('Error getting generation statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}

