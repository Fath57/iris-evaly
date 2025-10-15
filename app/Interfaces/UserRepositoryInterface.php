<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email);
    
    public function getUsersWithRoles(): Collection;
    
    public function getUsersByRole(string $role): Collection;
}

