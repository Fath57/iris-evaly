<?php

namespace App\Repositories;

use App\Interfaces\ClassRepositoryInterface;
use App\Models\ClassModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ClassRepository extends BaseRepository implements ClassRepositoryInterface
{
    public function __construct(ClassModel $model)
    {
        parent::__construct($model);
    }

    public function getActiveClasses(): Collection
    {
        return $this->model->where('is_active', true)->get();
    }

    public function getClassWithRelations(int $id)
    {
        return $this->model->with(['teachers', 'students', 'subjects'])->find($id);
    }

    public function attachStudents(int $classId, array $studentIds): void
    {
        DB::table('class_student')->where('class_id', $classId)->delete();
        
        foreach ($studentIds as $studentId) {
            DB::table('class_student')->insert([
                'class_id' => $classId,
                'student_id' => $studentId,
                'enrolled_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function attachTeachers(int $classId, array $teachers): void
    {
        DB::table('teacher_classes')->where('class_id', $classId)->delete();
        
        foreach ($teachers as $teacher) {
            DB::table('teacher_classes')->insert([
                'class_id' => $classId,
                'teacher_id' => $teacher['teacher_id'],
                'subject_id' => $teacher['subject_id'] ?? null,
                'is_main_teacher' => $teacher['is_main_teacher'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function paginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->query();

        // Search filter
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('code', 'like', "%{$filters['search']}%");
            });
        }

        // Level filter
        if (!empty($filters['level'])) {
            $query->where('level', $filters['level']);
        }

        // Active filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }
}

