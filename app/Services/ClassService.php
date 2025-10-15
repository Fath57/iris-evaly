<?php

namespace App\Services;

use App\Repositories\ClassRepository;
use Illuminate\Support\Facades\Log;

class ClassService
{
    protected ClassRepository $classRepository;

    public function __construct(ClassRepository $classRepository)
    {
        $this->classRepository = $classRepository;
    }

    public function getAllClasses()
    {
        return $this->classRepository->all();
    }

    public function getActiveClasses()
    {
        return $this->classRepository->getActiveClasses();
    }

    public function getClassesPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->classRepository->paginate($perPage, $filters);
    }

    public function createClass(array $data)
    {
        try {
            $class = $this->classRepository->create($data);

            Log::info('Class created', ['class_id' => $class->id, 'name' => $class->name]);

            return $class;
        } catch (\Exception $e) {
            Log::error('Error creating class', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateClass(int $id, array $data)
    {
        try {
            $this->classRepository->update($id, $data);

            Log::info('Class updated', ['class_id' => $id]);

            return $this->classRepository->find($id);
        } catch (\Exception $e) {
            Log::error('Error updating class', ['class_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteClass(int $id)
    {
        try {
            $result = $this->classRepository->delete($id);
            
            if ($result) {
                Log::info('Class deleted', ['class_id' => $id]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error deleting class', ['class_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getClassWithRelations(int $id)
    {
        return $this->classRepository->getClassWithRelations($id);
    }

    public function manageStudents(int $classId, array $studentIds)
    {
        try {
            $this->classRepository->attachStudents($classId, $studentIds);
            
            Log::info('Class students updated', ['class_id' => $classId, 'students_count' => count($studentIds)]);
        } catch (\Exception $e) {
            Log::error('Error managing class students', ['class_id' => $classId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function manageTeachers(int $classId, array $teachers)
    {
        try {
            $this->classRepository->attachTeachers($classId, $teachers);
            
            Log::info('Class teachers updated', ['class_id' => $classId, 'teachers_count' => count($teachers)]);
        } catch (\Exception $e) {
            Log::error('Error managing class teachers', ['class_id' => $classId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}

