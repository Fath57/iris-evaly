<?php

namespace App\Interfaces;

interface AIGenerationHistoryRepositoryInterface extends RepositoryInterface
{
    public function getHistoryByUser(int $userId);
    
    public function getHistoryByProvider(string $provider);
    
    public function getCompletedGenerations();
    
    public function getFailedGenerations();
    
    public function getTotalTokensUsed(int $userId);
    
    public function getTotalCost(int $userId);
    
    public function getAverageQualityRating(string $provider);
    
    public function getGenerationStatistics(int $userId);
}

