<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface ClassRepositoryInterface extends RepositoryInterface
{
    public function getActiveClasses(): Collection;
    
    public function getClassWithRelations(int $id);
    
    public function attachStudents(int $classId, array $studentIds): void;
    
    public function attachTeachers(int $classId, array $teachers): void;
}

