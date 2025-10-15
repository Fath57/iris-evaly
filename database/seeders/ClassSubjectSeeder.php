<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class ClassSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create subjects
        $subjects = [
            ['name' => 'Mathématiques', 'code' => 'MATH', 'description' => 'Cours de mathématiques', 'color' => '#3B82F6', 'is_active' => true],
            ['name' => 'Physique', 'code' => 'PHYS', 'description' => 'Cours de physique', 'color' => '#10B981', 'is_active' => true],
            ['name' => 'Chimie', 'code' => 'CHEM', 'description' => 'Cours de chimie', 'color' => '#F59E0B', 'is_active' => true],
            ['name' => 'Biologie', 'code' => 'BIO', 'description' => 'Cours de biologie', 'color' => '#8B5CF6', 'is_active' => true],
            ['name' => 'Informatique', 'code' => 'INFO', 'description' => 'Cours d\'informatique', 'color' => '#EF4444', 'is_active' => true],
            ['name' => 'Français', 'code' => 'FR', 'description' => 'Cours de français', 'color' => '#FF5E0E', 'is_active' => true],
            ['name' => 'Anglais', 'code' => 'EN', 'description' => 'Cours d\'anglais', 'color' => '#06B6D4', 'is_active' => true],
            ['name' => 'Histoire', 'code' => 'HIST', 'description' => 'Cours d\'histoire', 'color' => '#84CC16', 'is_active' => true],
        ];

        foreach ($subjects as $subject) {
            DB::table('subjects')->insert(array_merge($subject, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Get teachers
        $mathTeacher = User::where('email', 'prof.math@evaly.com')->first();
        $scienceTeacher = User::where('email', 'prof.sciences@evaly.com')->first();

        // Create classes
        $classes = [
            ['name' => 'Terminale S - Groupe A', 'code' => 'TS-A', 'description' => 'Classe de Terminale Scientifique - Groupe A', 'teacher_id' => $mathTeacher->id, 'academic_year' => 2024, 'level' => 'lycee', 'is_active' => true],
            ['name' => 'Terminale S - Groupe B', 'code' => 'TS-B', 'description' => 'Classe de Terminale Scientifique - Groupe B', 'teacher_id' => $scienceTeacher->id, 'academic_year' => 2024, 'level' => 'lycee', 'is_active' => true],
            ['name' => 'Première S', 'code' => '1S', 'description' => 'Classe de Première Scientifique', 'teacher_id' => $mathTeacher->id, 'academic_year' => 2024, 'level' => 'lycee', 'is_active' => true],
            ['name' => 'Seconde Générale', 'code' => '2ND', 'description' => 'Classe de Seconde Générale', 'teacher_id' => $scienceTeacher->id, 'academic_year' => 2024, 'level' => 'lycee', 'is_active' => true],
        ];

        foreach ($classes as $class) {
            $classId = DB::table('classes')->insertGetId(array_merge($class, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));

            // Add students to classes
            $students = User::role('student')->take(3)->get();
            foreach ($students as $student) {
                DB::table('class_student')->insert([
                    'class_id' => $classId,
                    'student_id' => $student->id,
                    'enrolled_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Add subjects to classes
            $subjectIds = [1, 2, 3, 4, 5]; // Math, Physics, Chemistry, Biology, Computer Science
            foreach ($subjectIds as $subjectId) {
                $teacherId = $subjectId <= 3 ? $mathTeacher->id : $scienceTeacher->id;
                DB::table('class_subject')->insert([
                    'class_id' => $classId,
                    'subject_id' => $subjectId,
                    'teacher_id' => $teacherId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
