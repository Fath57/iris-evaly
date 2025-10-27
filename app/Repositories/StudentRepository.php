<?php

namespace App\Repositories;

use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class StudentRepository implements StudentRepositoryInterface
{
    public function __construct(
        private Student $model
    ) {}

    public function getAll(): Collection
    {
        return $this->model->with('classes')->get();
    }

    public function getById(int $id): ?Student
    {
        return $this->model->with('classes')->find($id);
    }

    public function create(array $data): Student
    {
        return $this->model->create($data);
    }

    public function update(int $id, array $data): bool
    {
        return $this->model->where('id', $id)->update($data);
    }

    public function delete(int $id): bool
    {
        return $this->model->destroy($id);
    }

    public function getByClassId(int $classId): Collection
    {
        return $this->model->whereHas('classes', function ($query) use ($classId) {
            $query->where('class_id', $classId);
        })->with('classes')->get();
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with('classes');

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('first_name', 'like', "%{$filters['search']}%")
                  ->orWhere('last_name', 'like', "%{$filters['search']}%")
                  ->orWhere('email', 'like', "%{$filters['search']}%")
                  ->orWhere('student_number', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['class_id'])) {
            $query->whereHas('classes', function ($q) use ($filters) {
                $q->where('class_id', $filters['class_id']);
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findByEmail(string $email): ?Student
    {
        return $this->model->where('email', $email)->first();
    }

    public function findByStudentNumber(string $studentNumber): ?Student
    {
        return $this->model->where('student_number', $studentNumber)->first();
    }

    public function createWithClass(array $data, int $classId): Student
    {
        return DB::transaction(function () use ($data, $classId) {
            $student = $this->model->create($data);
            $student->classes()->attach($classId, ['enrolled_at' => now()]);
            return $student->load('classes');
        });
    }

    public function bulkImport(array $studentsData, int $classId): array
    {
        $results = ['success' => 0, 'errors' => []];

        DB::transaction(function () use ($studentsData, $classId, &$results) {
            foreach ($studentsData as $index => $studentData) {
                try {
                    // Check if student already exists
                    $existingStudent = $this->findByEmail($studentData['email'])
                        ?? $this->findByStudentNumber($studentData['student_number']);

                    if ($existingStudent) {
                        // Just assign to class if not already assigned
                        if (!$existingStudent->classes()->where('class_id', $classId)->exists()) {
                            $existingStudent->classes()->attach($classId, ['enrolled_at' => now()]);
                        }
                        $results['success']++;
                    } else {
                        // Create new student
                        $student = $this->model->create($studentData);
                        $student->classes()->attach($classId, ['enrolled_at' => now()]);
                        $results['success']++;
                    }
                } catch (\Exception $e) {
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'email' => $studentData['email'] ?? 'N/A',
                        'error' => $e->getMessage()
                    ];
                }
            }
        });

        return $results;
    }

    public function updatePassword(int $studentId, string $password): bool
    {
        return $this->model->where('id', $studentId)->update([
            'password' => Hash::make($password),
            'profile_completed' => true
        ]);
    }

    public function assignToClass(int $studentId, int $classId): bool
    {
        $student = $this->getById($studentId);
        if (!$student) return false;

        if (!$student->classes()->where('class_id', $classId)->exists()) {
            $student->classes()->attach($classId, ['enrolled_at' => now()]);
        }

        return true;
    }

    public function removeFromClass(int $studentId, int $classId): bool
    {
        $student = $this->getById($studentId);
        if (!$student) return false;

        $student->classes()->detach($classId);
        return true;
    }

    public function getActiveStudents(): Collection
    {
        return $this->model->active()->with('classes')->get();
    }

    public function search(string $query, array $columns = []): Collection
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%")
              ->orWhere('student_number', 'like', "%{$query}%");
        })->with('classes')->get();
    }

    public function all(): Collection
    {
        return $this->getAll();
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->getPaginated($filters, $perPage);
    }

    public function find(int $id): ?Student
    {
        return $this->getById($id);
    }
}
