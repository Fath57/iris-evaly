<?php

namespace App\Services;

use App\Interfaces\QuestionRepositoryInterface;
use App\Interfaces\QuestionOptionRepositoryInterface;
use App\Interfaces\ExamRepositoryInterface;
use App\Models\QuestionAnswer;
use App\Models\QuestionImage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class QuestionService
{
    protected $questionRepository;
    protected $optionRepository;
    protected $examRepository;

    public function __construct(
        QuestionRepositoryInterface $questionRepository,
        QuestionOptionRepositoryInterface $optionRepository,
        ExamRepositoryInterface $examRepository
    ) {
        $this->questionRepository = $questionRepository;
        $this->optionRepository = $optionRepository;
        $this->examRepository = $examRepository;
    }

    /**
     * Create a new question with options and answers.
     */
    public function createQuestion(array $data)
    {
        try {
            DB::beginTransaction();

            // Create the question
            $question = $this->questionRepository->create([
                'exam_id' => $data['exam_id'] ?? null,
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'points' => $data['points'] ?? 1,
                'order' => $data['order'] ?? 0,
                'explanation' => $data['explanation'] ?? null,
                'feedback' => $data['feedback'] ?? null,
                'category' => $data['category'] ?? null,
                'difficulty_level' => $data['difficulty_level'] ?? 'medium',
                'is_in_bank' => $data['is_in_bank'] ?? false,
                'formatting_options' => $data['formatting_options'] ?? null,
            ]);

            // Create options if provided
            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $index => $optionData) {
                    $option = $this->optionRepository->create([
                        'question_id' => $question->id,
                        'option_text' => $optionData['option_text'],
                        'option_key' => $optionData['option_key'] ?? chr(65 + $index), // A, B, C, D
                        'order' => $optionData['order'] ?? $index,
                        'formatting_options' => $optionData['formatting_options'] ?? null,
                    ]);

                    // Mark as correct answer if specified
                    if (isset($optionData['is_correct']) && $optionData['is_correct']) {
                        QuestionAnswer::create([
                            'question_id' => $question->id,
                            'option_id' => $option->id,
                            'is_correct' => true,
                            'explanation' => $optionData['answer_explanation'] ?? null,
                        ]);
                    }

                    // Handle option images
                    if (isset($optionData['images'])) {
                        foreach ($optionData['images'] as $imageData) {
                            $this->addImage($question->id, $imageData, $option->id);
                        }
                    }
                }
            }

            // Create text answer for open-ended questions
            if (isset($data['correct_answer_text'])) {
                QuestionAnswer::create([
                    'question_id' => $question->id,
                    'answer_text' => $data['correct_answer_text'],
                    'is_correct' => true,
                    'explanation' => $data['answer_explanation'] ?? null,
                ]);
            }

            // Handle question images
            if (isset($data['images'])) {
                foreach ($data['images'] as $imageData) {
                    $this->addImage($question->id, $imageData);
                }
            }

            // Update exam total points if question belongs to an exam
            if ($question->exam_id) {
                $this->examRepository->calculateTotalPoints($question->exam_id);
            }

            Log::info("Question created", ['question_id' => $question->id]);

            DB::commit();
            return $this->questionRepository->getFullQuestionData($question->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating question: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing question.
     */
    public function updateQuestion(int $questionId, array $data)
    {
        try {
            DB::beginTransaction();

            $question = $this->questionRepository->update($questionId, [
                'type' => $data['type'] ?? null,
                'question_text' => $data['question_text'] ?? null,
                'points' => $data['points'] ?? null,
                'order' => $data['order'] ?? null,
                'explanation' => $data['explanation'] ?? null,
                'feedback' => $data['feedback'] ?? null,
                'category' => $data['category'] ?? null,
                'difficulty_level' => $data['difficulty_level'] ?? null,
                'formatting_options' => $data['formatting_options'] ?? null,
            ]);

            // Update exam total points if question belongs to an exam
            if ($question->exam_id) {
                $this->examRepository->calculateTotalPoints($question->exam_id);
            }

            Log::info("Question updated", ['question_id' => $questionId]);

            DB::commit();
            return $this->questionRepository->getFullQuestionData($questionId);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating question: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a question.
     */
    public function deleteQuestion(int $questionId)
    {
        try {
            DB::beginTransaction();

            $question = $this->questionRepository->findById($questionId);
            $examId = $question->exam_id;

            // Delete associated images from storage
            foreach ($question->images as $image) {
                if (Storage::exists($image->image_path)) {
                    Storage::delete($image->image_path);
                }
            }

            $this->questionRepository->delete($questionId);

            // Update exam total points if question belonged to an exam
            if ($examId) {
                $this->examRepository->calculateTotalPoints($examId);
            }

            Log::info("Question deleted", ['question_id' => $questionId]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting question: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add question to bank.
     */
    public function addToBank(int $questionId)
    {
        try {
            $question = $this->questionRepository->addToBank($questionId);
            Log::info("Question added to bank", ['question_id' => $questionId]);
            return $question;
        } catch (\Exception $e) {
            Log::error('Error adding question to bank: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove question from bank.
     */
    public function removeFromBank(int $questionId)
    {
        try {
            $question = $this->questionRepository->removeFromBank($questionId);
            Log::info("Question removed from bank", ['question_id' => $questionId]);
            return $question;
        } catch (\Exception $e) {
            Log::error('Error removing question from bank: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Import question from bank to exam.
     */
    public function importToExam(int $questionId, int $examId)
    {
        try {
            $question = $this->questionRepository->importQuestionToExam($questionId, $examId);
            $this->examRepository->calculateTotalPoints($examId);
            
            Log::info("Question imported to exam", [
                'question_id' => $questionId,
                'exam_id' => $examId,
                'new_question_id' => $question->id
            ]);

            return $question;
        } catch (\Exception $e) {
            Log::error('Error importing question to exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Duplicate a question.
     */
    public function duplicateQuestion(int $questionId)
    {
        try {
            $question = $this->questionRepository->duplicateQuestion($questionId);
            
            Log::info("Question duplicated", [
                'original_id' => $questionId,
                'new_id' => $question->id
            ]);

            return $question;
        } catch (\Exception $e) {
            Log::error('Error duplicating question: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get questions by exam.
     */
    public function getQuestionsByExam(int $examId)
    {
        try {
            return $this->questionRepository->getQuestionsByExam($examId);
        } catch (\Exception $e) {
            Log::error('Error fetching questions by exam: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get questions in bank with filters.
     */
    public function getQuestionsInBank(array $filters = [])
    {
        try {
            $query = $this->questionRepository->getQuestionsInBank();

            if (isset($filters['category'])) {
                $query = $this->questionRepository->getQuestionsByCategory($filters['category']);
            }

            if (isset($filters['difficulty'])) {
                $query = $this->questionRepository->getQuestionsByDifficulty($filters['difficulty']);
            }

            if (isset($filters['type'])) {
                $query = $this->questionRepository->getQuestionsByType($filters['type']);
            }

            return $query;
        } catch (\Exception $e) {
            Log::error('Error fetching questions in bank: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get full question details.
     */
    public function getQuestionDetails(int $questionId)
    {
        try {
            return $this->questionRepository->getFullQuestionData($questionId);
        } catch (\Exception $e) {
            Log::error('Error fetching question details: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add image to question or option.
     */
    public function addImage(int $questionId, array $imageData, ?int $optionId = null)
    {
        try {
            $path = null;
            
            if (isset($imageData['file'])) {
                $path = $imageData['file']->store('questions/images', 'public');
            } elseif (isset($imageData['path'])) {
                $path = $imageData['path'];
            }

            $image = QuestionImage::create([
                'question_id' => $optionId ? null : $questionId,
                'option_id' => $optionId,
                'image_path' => $path,
                'image_type' => $imageData['image_type'] ?? 'question',
                'alt_text' => $imageData['alt_text'] ?? null,
                'width' => $imageData['width'] ?? null,
                'height' => $imageData['height'] ?? null,
                'order' => $imageData['order'] ?? 0,
            ]);

            Log::info("Image added", [
                'question_id' => $questionId,
                'option_id' => $optionId,
                'image_id' => $image->id
            ]);

            return $image;
        } catch (\Exception $e) {
            Log::error('Error adding image: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Remove image.
     */
    public function removeImage(int $imageId)
    {
        try {
            $image = QuestionImage::findOrFail($imageId);
            
            if (Storage::exists($image->image_path)) {
                Storage::delete($image->image_path);
            }

            $image->delete();

            Log::info("Image removed", ['image_id' => $imageId]);
        } catch (\Exception $e) {
            Log::error('Error removing image: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Reorder questions in an exam.
     */
    public function reorderQuestions(int $examId, array $questionIds)
    {
        try {
            DB::beginTransaction();

            foreach ($questionIds as $order => $questionId) {
                $this->questionRepository->update($questionId, ['order' => $order]);
            }

            Log::info("Questions reordered", ['exam_id' => $examId]);

            DB::commit();
            return $this->questionRepository->getQuestionsByExam($examId);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reordering questions: ' . $e->getMessage());
            throw $e;
        }
    }
}

