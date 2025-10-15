<?php

namespace App\Interfaces;

interface ExamAttemptRepositoryInterface extends RepositoryInterface
{
    public function getAttemptsByExam(int $examId);
    
    public function getAttemptsByStudent(int $studentId);
    
    public function getAttemptWithAnswers(int $attemptId);
    
    public function getStudentAttempts(int $studentId, int $examId);
    
    public function getInProgressAttempt(int $studentId, int $examId);
    
    public function completeAttempt(int $attemptId, array $data);
    
    public function calculateScore(int $attemptId);
    
    public function getExamStatistics(int $examId);
    
    public function getStudentStatistics(int $studentId);
}

