<?php

namespace Database\Seeders;

use App\Models\ClassModel;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClassStudentSeeder extends Seeder
{
    public function run(): void
    {
        $ts1 = ClassModel::where('code', 'TS1')->first();
        $ts2 = ClassModel::where('code', 'TS2')->first();

        $students = Student::all();

        if ($ts1 && $students->count() >= 3) {
            // Inscrire les 3 premiers étudiants dans TS1
            foreach ($students->take(3) as $student) {
                DB::table('class_student')->insertOrIgnore([
                    'class_id' => $ts1->id,
                    'student_id' => $student->id,
                    'enrolled_at' => '2024-09-01',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if ($ts2 && $students->count() >= 5) {
            // Inscrire les 2 derniers étudiants dans TS2
            foreach ($students->skip(3)->take(2) as $student) {
                DB::table('class_student')->insertOrIgnore([
                    'class_id' => $ts2->id,
                    'student_id' => $student->id,
                    'enrolled_at' => '2024-09-01',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
