<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_login()
    {
        // Create a student with a password
        $student = Student::factory()->create([
            'email' => 'student@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'profile_completed' => true
        ]);

        // Test login with correct credentials
        $response = $this->postJson('/api/students/login', [
            'email' => 'student@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Connexion réussie.'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'token'
                ],
                'message'
            ]);

        // Test login with incorrect password
        $response = $this->postJson('/api/students/login', [
            'email' => 'student@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Mot de passe incorrect.'
            ]);

        // Test login with non-existent email
        $response = $this->postJson('/api/students/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ]);
    }

    public function test_student_setup_password()
    {
        // Create a student without a password
        $student = Student::factory()->create([
            'email' => 'newstudent@example.com',
            'password' => null,
            'is_active' => true,
            'profile_completed' => false
        ]);

        // Test setting up password
        $response = $this->postJson("/api/students/{$student->id}/setup-password", [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Mot de passe défini avec succès. Vous êtes maintenant connecté.'
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'token'
                ],
                'message'
            ]);

        // Test login with new password
        $response = $this->postJson('/api/students/login', [
            'email' => 'newstudent@example.com',
            'password' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Connexion réussie.'
            ]);
    }

    public function test_student_profile_and_logout()
    {
        // Create a student
        $student = Student::factory()->create([
            'email' => 'profiletest@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'profile_completed' => true
        ]);

        // Login to get token
        $response = $this->postJson('/api/students/login', [
            'email' => 'profiletest@example.com',
            'password' => 'password123'
        ]);

        $token = $response->json('data.token');

        // Test getting profile
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/students/profile');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ])
            ->assertJsonStructure([
                'success',
                'data'
            ]);

        // Test updating profile
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson('/api/students/profile', [
                'first_name' => 'Updated',
                'last_name' => 'Name',
                'phone' => '+33987654321'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Profil mis à jour avec succès.'
            ]);

        // Verify profile was updated
        $this->assertDatabaseHas('students', [
            'id' => $student->id,
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '+33987654321'
        ]);

        // Test logout
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/students/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Déconnexion réussie.'
            ]);

        // Verify token is invalidated by trying to access profile again
        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/students/profile');

        $response->assertStatus(401);
    }
}
