<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use Importable;

    private $classId;
    private $students = [];

    public function __construct(int $classId)
    {
        $this->classId = $classId;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Store the data for later processing in the service
        $this->students[] = [
            'first_name' => $row['prenom'] ?? null,
            'last_name' => $row['nom'] ?? null,
            'email' => $row['email'] ?? null,
        ];

        // Return null as we'll handle the creation in the service
        return null;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:students,email',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'Le format de l\'email est invalide.',
            'email.unique' => 'Cet email est déjà utilisé par un autre étudiant.',
        ];
    }

    public function getStudents(): array
    {
        return $this->students;
    }

    public function getClassId(): int
    {
        return $this->classId;
    }
}
