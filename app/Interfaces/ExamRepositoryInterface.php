<?php

namespace App\Interfaces;

interface ExamRepositoryInterface extends RepositoryInterface
{
    public function getPublishedExams();
    
    public function getOngoingExams();
    
    public function getDraftExams();
    
    public function getExamsBySubject(int $subjectId);
    
    public function getExamsByClass(int $classId);
    
    public function getExamsCreatedBy(int $userId);
    
    public function getExamWithQuestions(int $examId);
    
    public function getExamWithAttempts(int $examId);
    
    public function updateExamStatus(int $examId, string $status);
    
    public function getAvailableExamsForStudent(int $studentId, int $classId);
    
    public function calculateTotalPoints(int $examId);
}

