<?php

namespace App\Interfaces;

use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface extends RepositoryInterface
{
    /**
     * Get students by class ID
     */
    public function getByClassId(int $classId): Collection;

    /**
     * Get students with pagination
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find student by email
     */
    public function findByEmail(string $email): ?Student;

    /**
     * Find student by student number
     */
    public function findByStudentNumber(string $studentNumber): ?Student;

    /**
     * Create student with class assignment
     */
    public function createWithClass(array $data, int $classId): Student;

    /**
     * Bulk import students
     */
    public function bulkImport(array $studentsData, int $classId): array;

    /**
     * Update student password
     */
    public function updatePassword(int $studentId, string $password): bool;

    /**
     * Assign student to class
     */
    public function assignToClass(int $studentId, int $classId): bool;

    /**
     * Remove student from class
     */
    public function removeFromClass(int $studentId, int $classId): bool;

    /**
     * Get active students
     */
    public function getActiveStudents(): Collection;

    /**
     * Search students by name or email
     */
    public function search(string $query, array $columns = []): Collection;
}
