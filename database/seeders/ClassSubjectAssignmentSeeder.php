<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassSubjectAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        })->first();

        $classes = ClassModel::all();
        $subjects = Subject::all();

        foreach ($classes as $class) {
            // Assigner toutes les matières à chaque classe
            foreach ($subjects as $subject) {
                DB::table('class_subject')->insertOrIgnore([
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $teacher?->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
