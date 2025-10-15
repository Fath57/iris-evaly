<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface RepositoryInterface
{
    public function all(): Collection;
    
    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;
    
    public function find(int $id): ?Model;
    
    public function create(array $data): Model;
    
    public function update(int $id, array $data): bool;
    
    public function delete(int $id): bool;
    
    public function search(string $query, array $columns = []): Collection;
}

