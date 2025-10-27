<?php

namespace App\Services;

use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudentService
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository
    ) {}

    /**
     * Get all students with pagination and filters
     */
    public function getStudents(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->studentRepository->getPaginated($filters, $perPage);
    }

    /**
     * Get student by ID
     */
    public function getStudent(int $id): ?Student
    {
        return $this->studentRepository->getById($id);
    }

    /**
     * Create a new student
     */
    public function createStudent(array $data): array
    {
        $validator = $this->validateStudentData($data);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        // Check for duplicates
        if ($this->studentRepository->findByEmail($data['email'])) {
            return [
                'success' => false,
                'errors' => ['email' => ['Un étudiant avec cet email existe déjà.']]
            ];
        }

        if (isset($data['student_number']) && $this->studentRepository->findByStudentNumber($data['student_number'])) {
            return [
                'success' => false,
                'errors' => ['student_number' => ['Un étudiant avec ce numéro existe déjà.']]
            ];
        }

        // Generate student number if not provided
        if (!isset($data['student_number'])) {
            $data['student_number'] = $this->generateStudentNumber();
        }

        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['enrollment_date'] = $data['enrollment_date'] ?? now();
        $data['profile_completed'] = isset($data['password']);

        $student = $this->studentRepository->create($data);

        return [
            'success' => true,
            'data' => $student
        ];
    }

    /**
     * Create student and assign to class
     */
    public function createStudentWithClass(array $data, int $classId): array
    {
        $validator = $this->validateStudentData($data);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        // Check for duplicates
        if ($this->studentRepository->findByEmail($data['email'])) {
            return [
                'success' => false,
                'errors' => ['email' => ['Un étudiant avec cet email existe déjà.']]
            ];
        }

        // Generate student number if not provided
        if (!isset($data['student_number'])) {
            $data['student_number'] = $this->generateStudentNumber();
        }

        // Set default values
        $data['is_active'] = $data['is_active'] ?? true;
        $data['enrollment_date'] = $data['enrollment_date'] ?? now();
        $data['profile_completed'] = isset($data['password']);

        $student = $this->studentRepository->createWithClass($data, $classId);

        return [
            'success' => true,
            'data' => $student
        ];
    }

    /**
     * Update student
     */
    public function updateStudent(int $id, array $data): array
    {
        $student = $this->studentRepository->getById($id);

        if (!$student) {
            return [
                'success' => false,
                'errors' => ['student' => ['Étudiant non trouvé.']]
            ];
        }

        $validator = $this->validateStudentData($data, $id);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        $success = $this->studentRepository->update($id, $data);

        return [
            'success' => $success,
            'data' => $success ? $this->studentRepository->getById($id) : null
        ];
    }

    /**
     * Set student password
     */
    public function setStudentPassword(int $studentId, string $password): array
    {
        $student = $this->studentRepository->getById($studentId);

        if (!$student) {
            return [
                'success' => false,
                'errors' => ['student' => ['Étudiant non trouvé.']]
            ];
        }

        if (strlen($password) < 8) {
            return [
                'success' => false,
                'errors' => ['password' => ['Le mot de passe doit contenir au moins 8 caractères.']]
            ];
        }

        $success = $this->studentRepository->updatePassword($studentId, $password);

        return [
            'success' => $success,
            'message' => $success ? 'Mot de passe défini avec succès.' : 'Erreur lors de la définition du mot de passe.'
        ];
    }

    /**
     * Bulk import students
     */
    public function importStudents(array $studentsData, int $classId): array
    {
        $validatedData = [];
        $errors = [];

        foreach ($studentsData as $index => $studentData) {
            $validator = $this->validateStudentData($studentData);

            if ($validator->fails()) {
                $errors[] = [
                    'row' => $index + 1,
                    'email' => $studentData['email'] ?? 'N/A',
                    'errors' => $validator->errors()->toArray()
                ];
                continue;
            }

            // Generate student number if not provided
            if (!isset($studentData['student_number'])) {
                $studentData['student_number'] = $this->generateStudentNumber();
            }

            // Set default values
            $studentData['is_active'] = $studentData['is_active'] ?? true;
            $studentData['enrollment_date'] = $studentData['enrollment_date'] ?? now();
            $studentData['profile_completed'] = false; // Will be set to true when password is defined

            $validatedData[] = $studentData;
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors
            ];
        }

        $results = $this->studentRepository->bulkImport($validatedData, $classId);

        return [
            'success' => true,
            'data' => $results
        ];
    }

    /**
     * Get students by class
     */
    public function getStudentsByClass(int $classId): Collection
    {
        return $this->studentRepository->getByClassId($classId);
    }

    /**
     * Search students
     */
    public function searchStudents(string $query): Collection
    {
        return $this->studentRepository->search($query);
    }

    /**
     * Validate student data
     */
    private function validateStudentData(array $data, ?int $excludeId = null): \Illuminate\Validation\Validator
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'student_number' => 'nullable|string|max:50',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'parent_contact' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
        ];

        if ($excludeId) {
            $rules['email'] .= "|unique:students,email,{$excludeId}";
            $rules['student_number'] .= "|unique:students,student_number,{$excludeId}";
        } else {
            $rules['email'] .= '|unique:students,email';
            $rules['student_number'] .= '|unique:students,student_number';
        }

        return Validator::make($data, $rules);
    }

    /**
     * Generate unique student number
     */
    private function generateStudentNumber(): string
    {
        do {
            $number = 'STU' . date('Y') . str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while ($this->studentRepository->findByStudentNumber($number));

        return $number;
    }

    /**
     * Assign student to a class
     */
    public function assignToClass(int $studentId, int $classId): array
    {
        $student = $this->studentRepository->getById($studentId);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ];
        }

        // Check if class exists (this would be better with a class repository)
        $class = \App\Models\ClassModel::find($classId);
        if (!$class) {
            return [
                'success' => false,
                'message' => 'Classe non trouvée.'
            ];
        }

        // Check if student is already in the class
        if ($student->classes()->where('class_id', $classId)->exists()) {
            return [
                'success' => true,
                'message' => 'L\'étudiant est déjà assigné à cette classe.',
                'data' => $student->load('classes')
            ];
        }

        $success = $this->studentRepository->assignToClass($studentId, $classId);

        return [
            'success' => $success,
            'message' => $success ? 'Étudiant assigné à la classe avec succès.' : 'Erreur lors de l\'assignation de l\'étudiant à la classe.',
            'data' => $success ? $this->studentRepository->getById($studentId) : null
        ];
    }
}
