<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teacherRole = Role::firstOrCreate(['name' => 'teacher']);

        $teachers = [
            [
                'name' => 'Marie Martin',
                'email' => 'marie.martin@evaly.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@evaly.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Sophie Bernard',
                'email' => 'sophie.bernard@evaly.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($teachers as $teacherData) {
            $teacher = User::firstOrCreate(
                ['email' => $teacherData['email']],
                $teacherData
            );
            $teacher->assignRole($teacherRole);
        }
    }
}
