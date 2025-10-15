<?php

namespace App\Imports;

use App\Models\Subject;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Log;

class SubjectsImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            return Subject::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'description' => $row['description'] ?? null,
                    'color' => $row['color'] ?? '#FF5E0E',
                    'is_active' => $row['is_active'] ?? true,
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error importing subject', [
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
            'color' => 'nullable|string|max:7',
            'is_active' => 'nullable|boolean',
        ];
    }

    /**
     * Custom validation messages
     */
    public function customValidationMessages()
    {
        return [
            'name.required' => 'Le nom de la matière est requis',
            'code.required' => 'Le code de la matière est requis',
        ];
    }
}
