<?php

namespace App\Imports;

use App\Models\ClassModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class ClassesImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            return ClassModel::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'academic_year' => $row['academic_year'] ?? date('Y'),
                    'level' => $row['level'] ?? 'lycee',
                    'is_active' => $row['is_active'] ?? true,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error importing class', [
                'row' => $row,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string',
            'academic_year' => 'nullable|integer|min:2000|max:2100',
            'level' => 'nullable|in:primaire,college,lycee,universite',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'Le nom de la classe est requis',
            'code.required' => 'Le code de la classe est requis',
            'level.in' => 'Le niveau doit être: primaire, college, lycee ou universite',
        ];
    }
}
