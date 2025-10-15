<?php

namespace App\Repositories;

use App\Interfaces\AIPromptRepositoryInterface;
use App\Models\AIPrompt;
use Illuminate\Support\Facades\Log;

class AIPromptRepository extends BaseRepository implements AIPromptRepositoryInterface
{
    public function __construct(AIPrompt $model)
    {
        parent::__construct($model);
    }

    public function getActivePrompts()
    {
        try {
            return $this->model->active()
                ->orderBy('usage_count', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching active prompts: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDefaultPrompts()
    {
        try {
            return $this->model->default()
                ->active()
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching default prompts: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPromptsByProvider(string $provider)
    {
        try {
            return $this->model->byProvider($provider)
                ->active()
                ->orderBy('average_quality_rating', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching prompts by provider: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getPromptsByQuestionType(string $type)
    {
        try {
            return $this->model->where('question_type', $type)
                ->active()
                ->orderBy('usage_count', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching prompts by question type: ' . $e->getMessage());
            throw $e;
        }
    }

    public function incrementUsage(int $promptId)
    {
        try {
            $prompt = $this->findById($promptId);
            $prompt->incrementUsage();
            
            Log::info("Prompt usage incremented", ['prompt_id' => $promptId]);
            
            return $prompt;
        } catch (\Exception $e) {
            Log::error('Error incrementing prompt usage: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateQualityRating(int $promptId, float $rating)
    {
        try {
            $prompt = $this->findById($promptId);
            $prompt->updateQualityRating($rating);
            
            Log::info("Prompt quality rating updated", [
                'prompt_id' => $promptId,
                'rating' => $rating
            ]);
            
            return $prompt;
        } catch (\Exception $e) {
            Log::error('Error updating prompt quality rating: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getMostUsedPrompts(int $limit = 10)
    {
        try {
            return $this->model->active()
                ->orderBy('usage_count', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching most used prompts: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getHighestRatedPrompts(int $limit = 10)
    {
        try {
            return $this->model->active()
                ->whereNotNull('average_quality_rating')
                ->orderBy('average_quality_rating', 'desc')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching highest rated prompts: ' . $e->getMessage());
            throw $e;
        }
    }
}

