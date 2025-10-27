<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface SubjectRepositoryInterface extends RepositoryInterface
{
    public function getActiveSubjects(): Collection;
    
    public function findByCode(string $code);
}







