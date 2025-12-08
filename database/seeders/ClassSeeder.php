<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use Illuminate\Database\Seeder;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            [
                'name' => 'Terminale S1',
                'code' => 'TS1',
                'description' => 'Classe de Terminale Scientifique 1',
                'academic_year' => 2025,
                'level' => 'lycee',
                'is_active' => true,
            ],
            [
                'name' => 'Terminale S2',
                'code' => 'TS2',
                'description' => 'Classe de Terminale Scientifique 2',
                'academic_year' => 2025,
                'level' => 'lycee',
                'is_active' => true,
            ],
            [
                'name' => 'Première S1',
                'code' => '1S1',
                'description' => 'Classe de Première Scientifique 1',
                'academic_year' => 2025,
                'level' => 'lycee',
                'is_active' => true,
            ],
        ];

        foreach ($classes as $classData) {
            ClassModel::firstOrCreate(
                ['code' => $classData['code']],
                $classData
            );
        }
    }
}
