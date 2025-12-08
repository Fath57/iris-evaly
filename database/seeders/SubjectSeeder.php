<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Mathématiques',
                'code' => 'MATH',
                'description' => 'Algèbre, géométrie, analyse et statistiques',
                'color' => '#3B82F6',
                'is_active' => true,
            ],
            [
                'name' => 'Français',
                'code' => 'FR',
                'description' => 'Grammaire, littérature et expression écrite',
                'color' => '#EF4444',
                'is_active' => true,
            ],
            [
                'name' => 'Histoire-Géographie',
                'code' => 'HG',
                'description' => 'Histoire et géographie du monde',
                'color' => '#F59E0B',
                'is_active' => true,
            ],
            [
                'name' => 'Physique-Chimie',
                'code' => 'PC',
                'description' => 'Sciences physiques et chimiques',
                'color' => '#10B981',
                'is_active' => true,
            ],
            [
                'name' => 'Sciences de la Vie et de la Terre',
                'code' => 'SVT',
                'description' => 'Biologie et sciences de la terre',
                'color' => '#22C55E',
                'is_active' => true,
            ],
            [
                'name' => 'Anglais',
                'code' => 'ANG',
                'description' => 'Langue anglaise et civilisation',
                'color' => '#6366F1',
                'is_active' => true,
            ],
        ];

        foreach ($subjects as $subjectData) {
            Subject::firstOrCreate(
                ['code' => $subjectData['code']],
                $subjectData
            );
        }
    }
}
