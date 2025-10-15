<?php

namespace App\Interfaces;

interface AIPromptRepositoryInterface extends RepositoryInterface
{
    public function getActivePrompts();
    
    public function getDefaultPrompts();
    
    public function getPromptsByProvider(string $provider);
    
    public function getPromptsByQuestionType(string $type);
    
    public function incrementUsage(int $promptId);
    
    public function updateQualityRating(int $promptId, float $rating);
    
    public function getMostUsedPrompts(int $limit = 10);
    
    public function getHighestRatedPrompts(int $limit = 10);
}

