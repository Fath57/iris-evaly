<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create super admin
        $superAdmin = User::create([
            'name' => 'Super Administrateur',
            'email' => 'admin@evaly.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super-admin');

        // Create admin
        $admin = User::create([
            'name' => 'Administrateur',
            'email' => 'admin2@evaly.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Create teachers
        $teacher1 = User::create([
            'name' => 'Professeur Math',
            'email' => 'prof.math@evaly.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $teacher1->assignRole('teacher');

        $teacher2 = User::create([
            'name' => 'Professeur Sciences',
            'email' => 'prof.sciences@evaly.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $teacher2->assignRole('teacher');

        // Create assistant
        $assistant = User::create([
            'name' => 'Assistant Pédagogique',
            'email' => 'assistant@evaly.com',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
        ]);
        $assistant->assignRole('assistant');

        // Create students
        for ($i = 1; $i <= 5; $i++) {
            $student = User::create([
                'name' => "Étudiant Test $i",
                'email' => "etudiant$i@evaly.com",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            $student->assignRole('student');
        }
    }
}
