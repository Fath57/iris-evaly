<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\ClassModel;
use App\Models\User;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_import_endpoint()
    {
        // Create a test class
        $class = ClassModel::factory()->create([
            'name' => 'Test Class',
            'code' => 'TC101',
            'is_active' => true
        ]);

        // Create a test admin user
        $user = User::factory()->create();
        $user->assignRole('admin');

        // Create a fake Excel file with student data
        Storage::fake('local');
        $header = "prenom,nom,email,telephone,date_naissance\n";
        $row1 = "Jean,Dupont,jean.dupont@example.com,+33123456789,2000-01-01\n";
        $row2 = "Marie,Martin,marie.martin@example.com,+33987654321,2001-02-02\n";

        $content = $header . $row1 . $row2;
        $file = UploadedFile::fake()->createWithContent('students.csv', $content);

        // Send request to import endpoint
        $response = $this->actingAs($user)
            ->post("/api/students/import/{$class->id}", [
                'file' => $file
            ]);

        // Assert response is successful
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Import réalisé avec succès.'
            ]);

        // Assert students were created in database
        $this->assertDatabaseHas('students', [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'email' => 'jean.dupont@example.com',
            'phone' => '+33123456789',
        ]);

        $this->assertDatabaseHas('students', [
            'first_name' => 'Marie',
            'last_name' => 'Martin',
            'email' => 'marie.martin@example.com',
            'phone' => '+33987654321',
        ]);

        // Assert students are assigned to the class
        $this->assertDatabaseHas('class_student', [
            'class_id' => $class->id,
        ]);
    }
}
