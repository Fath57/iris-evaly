<?php

namespace App\Interfaces;

interface QuestionOptionRepositoryInterface extends RepositoryInterface
{
    public function getOptionsByQuestion(int $questionId);
    
    public function reorderOptions(int $questionId, array $optionIds);
    
    public function getOptionWithImages(int $optionId);
}

