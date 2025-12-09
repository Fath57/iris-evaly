<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            // Étudiants avec mot de passe défini (prêts à se connecter)
            [
                'student_number' => 'STU202500001',
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0612345678',
                'date_of_birth' => '2005-03-15',
                'is_active' => true,
                'enrollment_date' => '2024-09-01',
                'profile_completed' => true,
            ],
            [
                'student_number' => 'STU202500002',
                'first_name' => 'Emma',
                'last_name' => 'Leroy',
                'email' => 'emma.leroy@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0623456789',
                'date_of_birth' => '2005-06-22',
                'is_active' => true,
                'enrollment_date' => '2024-09-01',
                'profile_completed' => true,
            ],
            [
                'student_number' => 'STU202500003',
                'first_name' => 'Lucas',
                'last_name' => 'Moreau',
                'email' => 'lucas.moreau@test.com',
                'password' => Hash::make('password123'),
                'phone' => '0634567890',
                'date_of_birth' => '2005-01-10',
                'is_active' => true,
                'enrollment_date' => '2024-09-01',
                'profile_completed' => true,
            ],
            // Étudiants sans mot de passe (doivent faire le setup)
            [
                'student_number' => 'STU202500004',
                'first_name' => 'Chloé',
                'last_name' => 'Petit',
                'email' => 'chloe.petit@test.com',
                'password' => null,
                'phone' => '0645678901',
                'date_of_birth' => '2005-09-05',
                'is_active' => true,
                'enrollment_date' => '2024-09-01',
                'profile_completed' => false,
            ],
            [
                'student_number' => 'STU202500005',
                'first_name' => 'Hugo',
                'last_name' => 'Roux',
                'email' => 'hugo.roux@test.com',
                'password' => null,
                'phone' => '0656789012',
                'date_of_birth' => '2005-12-20',
                'is_active' => true,
                'enrollment_date' => '2024-09-01',
                'profile_completed' => false,
            ],
        ];

        foreach ($students as $studentData) {
            Student::firstOrCreate(
                ['email' => $studentData['email']],
                $studentData
            );
        }
    }
}
