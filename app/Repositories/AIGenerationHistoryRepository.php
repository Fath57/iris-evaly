<?php

namespace App\Repositories;

use App\Interfaces\AIGenerationHistoryRepositoryInterface;
use App\Models\AIGenerationHistory;
use Illuminate\Support\Facades\Log;

class AIGenerationHistoryRepository extends BaseRepository implements AIGenerationHistoryRepositoryInterface
{
    public function __construct(AIGenerationHistory $model)
    {
        parent::__construct($model);
    }

    public function getHistoryByUser(int $userId)
    {
        try {
            return $this->model->where('user_id', $userId)
                ->with('prompt')
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching history by user: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getHistoryByProvider(string $provider)
    {
        try {
            return $this->model->byProvider($provider)
                ->with(['user', 'prompt'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching history by provider: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getCompletedGenerations()
    {
        try {
            return $this->model->completed()
                ->with(['user', 'prompt'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching completed generations: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getFailedGenerations()
    {
        try {
            return $this->model->failed()
                ->with(['user', 'prompt'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching failed generations: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalTokensUsed(int $userId)
    {
        try {
            return $this->model->where('user_id', $userId)
                ->completed()
                ->sum('tokens_used');
        } catch (\Exception $e) {
            Log::error('Error calculating total tokens used: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getTotalCost(int $userId)
    {
        try {
            return $this->model->where('user_id', $userId)
                ->completed()
                ->sum('cost');
        } catch (\Exception $e) {
            Log::error('Error calculating total cost: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAverageQualityRating(string $provider)
    {
        try {
            return $this->model->byProvider($provider)
                ->completed()
                ->whereNotNull('quality_rating')
                ->avg('quality_rating');
        } catch (\Exception $e) {
            Log::error('Error calculating average quality rating: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getGenerationStatistics(int $userId)
    {
        try {
            $history = $this->model->where('user_id', $userId)->get();
            
            return [
                'total_generations' => $history->count(),
                'completed' => $history->where('status', 'completed')->count(),
                'failed' => $history->where('status', 'failed')->count(),
                'total_questions_generated' => $history->sum('questions_generated'),
                'total_questions_accepted' => $history->sum('questions_accepted'),
                'average_acceptance_rate' => $history->avg('acceptance_rate'),
                'total_tokens_used' => $history->sum('tokens_used'),
                'total_cost' => $history->sum('cost'),
                'average_quality_rating' => $history->whereNotNull('quality_rating')->avg('quality_rating'),
            ];
        } catch (\Exception $e) {
            Log::error('Error getting generation statistics: ' . $e->getMessage());
            throw $e;
        }
    }
}

