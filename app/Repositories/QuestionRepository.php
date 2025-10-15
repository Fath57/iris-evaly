<?php

namespace App\Repositories;

use App\Interfaces\QuestionRepositoryInterface;
use App\Models\Question;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

    public function getQuestionsByExam(int $examId)
    {
        try {
            return $this->model->where('exam_id', $examId)
                ->orderBy('order')
                ->with(['options', 'correctAnswers', 'images'])
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching questions by exam: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionsInBank()
    {
        try {
            return $this->model->inBank()
                ->with(['options', 'correctAnswers', 'images'])
                ->orderBy('created_at', 'desc')
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching questions in bank: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionsByCategory(string $category)
    {
        try {
            return $this->model->byCategory($category)
                ->with(['options', 'correctAnswers', 'images'])
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching questions by category: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionsByDifficulty(string $difficulty)
    {
        try {
            return $this->model->byDifficulty($difficulty)
                ->with(['options', 'correctAnswers', 'images'])
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching questions by difficulty: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionsByType(string $type)
    {
        try {
            return $this->model->byType($type)
                ->with(['options', 'correctAnswers', 'images'])
                ->get();
        } catch (\Exception $e) {
            Log::error('Error fetching questions by type: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionWithOptions(int $questionId)
    {
        try {
            return $this->model->with(['options' => function ($query) {
                $query->orderBy('order');
            }])->findOrFail($questionId);
        } catch (\Exception $e) {
            Log::error('Error fetching question with options: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionWithAnswers(int $questionId)
    {
        try {
            return $this->model->with(['correctAnswers', 'correctAnswers.option'])
                ->findOrFail($questionId);
        } catch (\Exception $e) {
            Log::error('Error fetching question with answers: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getQuestionWithImages(int $questionId)
    {
        try {
            return $this->model->with(['images', 'options.images'])
                ->findOrFail($questionId);
        } catch (\Exception $e) {
            Log::error('Error fetching question with images: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getFullQuestionData(int $questionId)
    {
        try {
            return $this->model->with([
                'options' => function ($query) {
                    $query->orderBy('order');
                },
                'options.images',
                'correctAnswers',
                'correctAnswers.option',
                'images',
            ])->findOrFail($questionId);
        } catch (\Exception $e) {
            Log::error('Error fetching full question data: ' . $e->getMessage());
            throw $e;
        }
    }

    public function addToBank(int $questionId)
    {
        try {
            $question = $this->findById($questionId);
            $question->is_in_bank = true;
            $question->save();
            
            Log::info("Question added to bank", ['question_id' => $questionId]);
            
            return $question;
        } catch (\Exception $e) {
            Log::error('Error adding question to bank: ' . $e->getMessage());
            throw $e;
        }
    }

    public function removeFromBank(int $questionId)
    {
        try {
            $question = $this->findById($questionId);
            $question->is_in_bank = false;
            $question->save();
            
            Log::info("Question removed from bank", ['question_id' => $questionId]);
            
            return $question;
        } catch (\Exception $e) {
            Log::error('Error removing question from bank: ' . $e->getMessage());
            throw $e;
        }
    }

    public function duplicateQuestion(int $questionId)
    {
        try {
            DB::beginTransaction();
            
            $originalQuestion = $this->getFullQuestionData($questionId);
            
            // Create new question
            $newQuestion = $originalQuestion->replicate();
            $newQuestion->exam_id = null; // Put in bank by default
            $newQuestion->is_in_bank = true;
            $newQuestion->save();
            
            // Duplicate options
            foreach ($originalQuestion->options as $option) {
                $newOption = $option->replicate();
                $newOption->question_id = $newQuestion->id;
                $newOption->save();
                
                // Duplicate option images
                foreach ($option->images as $image) {
                    $newImage = $image->replicate();
                    $newImage->option_id = $newOption->id;
                    $newImage->question_id = null;
                    $newImage->save();
                }
                
                // Update correct answers
                $correctAnswer = $originalQuestion->correctAnswers
                    ->where('option_id', $option->id)
                    ->first();
                    
                if ($correctAnswer) {
                    $newCorrectAnswer = $correctAnswer->replicate();
                    $newCorrectAnswer->question_id = $newQuestion->id;
                    $newCorrectAnswer->option_id = $newOption->id;
                    $newCorrectAnswer->save();
                }
            }
            
            // Duplicate question images
            foreach ($originalQuestion->images->where('option_id', null) as $image) {
                $newImage = $image->replicate();
                $newImage->question_id = $newQuestion->id;
                $newImage->save();
            }
            
            DB::commit();
            
            Log::info("Question duplicated", [
                'original_id' => $questionId,
                'new_id' => $newQuestion->id
            ]);
            
            return $newQuestion;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error duplicating question: ' . $e->getMessage());
            throw $e;
        }
    }

    public function importQuestionToExam(int $questionId, int $examId)
    {
        try {
            $duplicatedQuestion = $this->duplicateQuestion($questionId);
            $duplicatedQuestion->exam_id = $examId;
            $duplicatedQuestion->is_in_bank = true; // Keep in bank
            $duplicatedQuestion->save();
            
            Log::info("Question imported to exam", [
                'question_id' => $questionId,
                'exam_id' => $examId,
                'new_question_id' => $duplicatedQuestion->id
            ]);
            
            return $duplicatedQuestion;
        } catch (\Exception $e) {
            Log::error('Error importing question to exam: ' . $e->getMessage());
            throw $e;
        }
    }
}

