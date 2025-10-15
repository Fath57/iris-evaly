<?php

namespace App\Repositories;

use App\Interfaces\QuestionOptionRepositoryInterface;
use App\Models\QuestionOption;
use Illuminate\Support\Facades\Log;

class QuestionOptionRepository extends BaseRepository implements QuestionOptionRepositoryInterface
{
    public function __construct(QuestionOption $model)
    {
        parent::__construct($model);
    }

    public function getOptionsByQuestion(int $questionId)
    {
        try {
            return $this->model->where('question_id', $questionId)
                ->orderBy('order')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching options by question: ' . $e->getMessage());
            throw $e;
        }
    }

    public function reorderOptions(int $questionId, array $optionIds)
    {
        try {
            foreach ($optionIds as $order => $optionId) {
                $this->model->where('id', $optionId)
                    ->where('question_id', $questionId)
                    ->update(['order' => $order]);
            }
            
            Log::info("Options reordered", ['question_id' => $questionId]);
            
            return $this->getOptionsByQuestion($questionId);
        } catch (\Exception $e) {
            Log::error('Error reordering options: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getOptionWithImages(int $optionId)
    {
        try {
            return $this->model->with('images')->findOrFail($optionId);
        } catch (\Exception $e) {
            Log::error('Error fetching option with images: ' . $e->getMessage());
            throw $e;
        }
    }
}

