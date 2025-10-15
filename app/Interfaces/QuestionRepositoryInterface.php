<?php

namespace App\Interfaces;

interface QuestionRepositoryInterface extends RepositoryInterface
{
    public function getQuestionsByExam(int $examId);
    
    public function getQuestionsInBank();
    
    public function getQuestionsByCategory(string $category);
    
    public function getQuestionsByDifficulty(string $difficulty);
    
    public function getQuestionsByType(string $type);
    
    public function getQuestionWithOptions(int $questionId);
    
    public function getQuestionWithAnswers(int $questionId);
    
    public function getQuestionWithImages(int $questionId);
    
    public function getFullQuestionData(int $questionId);
    
    public function addToBank(int $questionId);
    
    public function removeFromBank(int $questionId);
    
    public function duplicateQuestion(int $questionId);
    
    public function importQuestionToExam(int $questionId, int $examId);
}

