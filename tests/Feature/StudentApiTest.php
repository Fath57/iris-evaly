<?php

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Exam;
use App\Models\Question;
use App\Models\QuestionAnswer;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create a class for testing
    $this->class = ClassModel::create([
        'name' => 'Test Class',
        'code' => 'TST01',
        'description' => 'Test class for student API tests',
        'level' => 'lycee',
        'academic_year' => '2025-2026'
    ]);

    // Create a subject for testing
    $this->subject = Subject::create([
        'name' => 'Test Subject',
        'description' => 'Test subject for exams',
        'code' => 'TST01'
    ]);

    // Create a teacher
    $this->teacher = User::create([
        'name' => 'Test Teacher',
        'email' => 'teacher@test.com',
        'password' => Hash::make('password123'),
    ]);

    // Create a student without password (to test setup password)
    $this->studentWithoutPassword = Student::create([
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john.doe@test.com',
        'student_number' => 'STU202500001',
        'password' => null,
        'profile_completed' => false,
        'is_active' => true,
        'enrollment_date' => now(),
    ]);
    $this->studentWithoutPassword->classes()->attach($this->class->id);

    // Create a student with password (for login tests)
    $this->student = Student::create([
        'first_name' => 'Jane',
        'last_name' => 'Smith',
        'email' => 'jane.smith@test.com',
        'student_number' => 'STU202500002',
        'password' => Hash::make('password123'),
        'profile_completed' => true,
        'is_active' => true,
        'enrollment_date' => now(),
    ]);
    $this->student->classes()->attach($this->class->id);
});

describe('Student Authentication', function () {
    test('student can setup password for first time', function () {
        $response = $this->postJson("/api/students/setup-password", [
            'email' => 'john.doe@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'token',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Verify password was set
        $this->studentWithoutPassword->refresh();
        expect(Hash::check('newpassword123', $this->studentWithoutPassword->password))->toBeTrue();
    });

    test('student cannot setup password with mismatched confirmation', function () {
        $response = $this->postJson("/api/students/setup-password", [
            'email' => 'john.doe@test.com',
            'password' => 'newpassword123',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    });

    test('student can login with valid credentials', function () {
        $response = $this->postJson('/api/students/login', [
            'email' => 'jane.smith@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'student',
                    'token',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);
    });

    test('student cannot login with invalid credentials', function () {
        $response = $this->postJson('/api/students/login', [
            'email' => 'jane.smith@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
            ]);
    });

    test('student without password setup gets appropriate message on login', function () {
        $response = $this->postJson('/api/students/login', [
            'email' => 'john.doe@test.com',
            'password' => 'anypassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'requires_password_setup' => true,
            ]);
    });
});

describe('Student Profile', function () {
    test('authenticated student can get their profile', function () {
        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/students/profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                    'student_number',
                ],
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'email' => 'jane.smith@test.com',
                ],
            ]);
    });

    test('unauthenticated request cannot access profile', function () {
        $response = $this->getJson('/api/students/profile');

        $response->assertStatus(401);
    });

    test('authenticated student can update their profile', function () {
        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->putJson('/api/students/profile', [
            'first_name' => 'Updated',
            'last_name' => 'Name',
            'phone' => '+33123456789',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->student->refresh();
        expect($this->student->first_name)->toBe('Updated');
        expect($this->student->last_name)->toBe('Name');
        expect($this->student->phone)->toBe('+33123456789');
    });
});

describe('Student Password Management', function () {
    test('authenticated student can change their password', function () {
        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/students/change-password', [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        // Verify new password works
        $this->student->refresh();
        expect(Hash::check('newpassword456', $this->student->password))->toBeTrue();
    });

    test('student cannot change password with wrong current password', function () {
        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/students/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword456',
            'password_confirmation' => 'newpassword456',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    });

    test('student cannot change password with mismatched confirmation', function () {
        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/students/change-password', [
            'current_password' => 'password123',
            'password' => 'newpassword456',
            'password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    });
});

describe('Student Exams', function () {
    test('authenticated student can get their exams', function () {
        // Create upcoming exam
        $upcomingExam = Exam::create([
            'title' => 'Upcoming Exam',
            'description' => 'Test upcoming exam',
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'status' => 'published',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'duration_minutes' => 120,
            'total_points' => 100,
            'passing_score' => 50,
            'max_attempts' => 2,
        ]);

        // Create ongoing exam
        $ongoingExam = Exam::create([
            'title' => 'Ongoing Exam',
            'description' => 'Test ongoing exam',
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'status' => 'published',
            'start_date' => now()->subHour(),
            'end_date' => now()->addHour(),
            'duration_minutes' => 120,
            'total_points' => 100,
            'passing_score' => 50,
            'max_attempts' => 1,
        ]);

        // Create past exam
        $pastExam = Exam::create([
            'title' => 'Past Exam',
            'description' => 'Test past exam',
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'status' => 'published',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(5)->addHours(2),
            'duration_minutes' => 120,
            'total_points' => 100,
            'passing_score' => 50,
            'max_attempts' => 2,
        ]);

        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/students/exams');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'upcoming' => [
                        '*' => [
                            'id',
                            'title',
                            'description',
                            'subject',
                            'class',
                            'duration',
                            'total_points',
                            'passing_score',
                            'start_date',
                            'end_date',
                            'questions_count',
                            'max_attempts',
                            'attempt_count',
                            'can_attempt',
                            'best_score',
                            'last_attempt',
                        ],
                    ],
                    'ongoing',
                    'past',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Verify exams are categorized correctly
        $data = $response->json('data');
        expect($data['upcoming'])->toHaveCount(1);
        expect($data['ongoing'])->toHaveCount(1);
        expect($data['past'])->toHaveCount(1);

        expect($data['upcoming'][0]['title'])->toBe('Upcoming Exam');
        expect($data['ongoing'][0]['title'])->toBe('Ongoing Exam');
        expect($data['past'][0]['title'])->toBe('Past Exam');
    });

    test('student with no classes gets empty exams list', function () {
        $studentWithoutClass = Student::create([
            'first_name' => 'No',
            'last_name' => 'Class',
            'email' => 'noclass@test.com',
            'student_number' => 'STU202500003',
            'password' => Hash::make('password123'),
            'profile_completed' => true,
            'is_active' => true,
            'enrollment_date' => now(),
        ]);

        $token = $studentWithoutClass->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/students/exams');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'upcoming' => [],
                    'ongoing' => [],
                    'past' => [],
                ],
            ]);
    });

    test('unauthenticated request cannot access exams', function () {
        $response = $this->getJson('/api/students/exams');

        $response->assertStatus(401);
    });
});

describe('Student Exam Attempts', function () {
    test('student can submit an exam and retrieve results', function () {
        $exam = Exam::create([
            'title' => 'Math Exam',
            'description' => 'Algebra basics',
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'status' => 'published',
            'start_date' => now()->subMinutes(10),
            'end_date' => now()->addHour(),
            'duration_minutes' => 90,
            'total_points' => 10,
            'passing_score' => 50,
            'max_attempts' => 2,
        ]);

        $questionOne = Question::create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'question_text' => 'Combien font 2 + 2 ?',
            'points' => 5,
            'order' => 1,
        ]);

        $optionOneQ1 = QuestionOption::create([
            'question_id' => $questionOne->id,
            'option_text' => '3',
            'option_key' => 'A',
            'order' => 1,
        ]);

        $optionTwoQ1 = QuestionOption::create([
            'question_id' => $questionOne->id,
            'option_text' => '4',
            'option_key' => 'B',
            'order' => 2,
        ]);

        QuestionAnswer::create([
            'question_id' => $questionOne->id,
            'option_id' => $optionTwoQ1->id,
            'is_correct' => true,
        ]);

        $questionTwo = Question::create([
            'exam_id' => $exam->id,
            'type' => 'true_false',
            'question_text' => '0 est un nombre pair.',
            'points' => 5,
            'order' => 2,
        ]);

        $optionTrue = QuestionOption::create([
            'question_id' => $questionTwo->id,
            'option_text' => 'Vrai',
            'option_key' => 'A',
            'order' => 1,
        ]);

        $optionFalse = QuestionOption::create([
            'question_id' => $questionTwo->id,
            'option_text' => 'Faux',
            'option_key' => 'B',
            'order' => 2,
        ]);

        QuestionAnswer::create([
            'question_id' => $questionTwo->id,
            'option_id' => $optionTrue->id,
            'is_correct' => true,
        ]);

        $token = $this->student->createToken('exam-token')->plainTextToken;

        $submitResponse = $this->withToken($token)->postJson("/api/students/exams/{$exam->id}/submit", [
            'time_spent_seconds' => 300,
            'answers' => [
                [
                    'question_id' => $questionOne->id,
                    'option_id' => $optionTwoQ1->id,
                    'time_spent_seconds' => 120,
                ],
                [
                    'question_id' => $questionTwo->id,
                    'option_id' => $optionTrue->id,
                ],
            ],
        ]);

        $submitResponse->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'score' => 10.0,
                    'percentage' => 100.0,
                    'passed' => true,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'attempt_id',
                    'score',
                    'percentage',
                    'passed',
                    'summary' => [
                        'total_questions',
                        'answered_questions',
                        'correct_answers',
                        'total_score',
                        'percentage',
                        'passed',
                        'time_spent',
                    ],
                    'answers',
                ],
                'message',
            ]);

        $resultsResponse = $this->withToken($token)->getJson("/api/students/exams/{$exam->id}/results");

        $resultsResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'score' => 10.0,
                    'percentage' => 100.0,
                    'passed' => true,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'attempt_id',
                    'score',
                    'percentage',
                    'passed',
                    'summary',
                    'answers',
                    'exam' => [
                        'id',
                        'title',
                        'description',
                        'total_points',
                        'passing_score',
                    ],
                ],
                'message',
            ]);
    });

    test('student cannot submit exam for a class they are not enrolled in', function () {
        $otherClass = ClassModel::create([
            'name' => 'Other Class',
            'code' => 'OTH01',
            'description' => 'Other class',
            'level' => 'lycee',
            'academic_year' => '2025-2026',
        ]);

        $exam = Exam::create([
            'title' => 'Physics Exam',
            'description' => 'Mechanics',
            'class_id' => $otherClass->id,
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'status' => 'published',
            'start_date' => now()->subHour(),
            'end_date' => now()->addHour(),
            'duration_minutes' => 60,
            'total_points' => 10,
            'passing_score' => 50,
            'max_attempts' => 1,
        ]);

        $question = Question::create([
            'exam_id' => $exam->id,
            'type' => 'multiple_choice',
            'question_text' => 'Question hors classe',
            'points' => 10,
            'order' => 1,
        ]);

        $option = QuestionOption::create([
            'question_id' => $question->id,
            'option_text' => 'Réponse',
            'option_key' => 'A',
            'order' => 1,
        ]);

        QuestionAnswer::create([
            'question_id' => $question->id,
            'option_id' => $option->id,
            'is_correct' => true,
        ]);

        $token = $this->student->createToken('exam-token')->plainTextToken;

        $response = $this->withToken($token)->postJson("/api/students/exams/{$exam->id}/submit", [
            'answers' => [
                [
                    'question_id' => $question->id,
                    'option_id' => $option->id,
                ],
            ],
        ]);

        $response->assertStatus(403);
    });

    test('student cannot retrieve results when no attempt exists', function () {
        $exam = Exam::create([
            'title' => 'History Exam',
            'description' => 'Ancient history',
            'class_id' => $this->class->id,
            'subject_id' => $this->subject->id,
            'created_by' => $this->teacher->id,
            'status' => 'published',
            'start_date' => now()->subHour(),
            'end_date' => now()->addHour(),
            'duration_minutes' => 60,
            'total_points' => 10,
            'passing_score' => 50,
            'max_attempts' => 1,
        ]);

        $token = $this->student->createToken('exam-token')->plainTextToken;

        $response = $this->withToken($token)->getJson("/api/students/exams/{$exam->id}/results");

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
            ]);
    });
});

describe('Student Logout', function () {
    test('authenticated student can logout', function () {
        $token = $this->student->createToken('test-token')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/students/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        dump(\Laravel\Sanctum\PersonalAccessToken::count());
        // Verify token is revoked
        $response = $this->withToken($token)->getJson('/api/students/profile');
        dump($response->json());
        $response->assertStatus(401);
    });
});
