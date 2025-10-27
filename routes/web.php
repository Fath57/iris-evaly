<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ClassController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\ProfileSetupController;
use App\Http\Controllers\Teacher\TeacherDashboardController;
use App\Http\Controllers\Admin\TeacherAssignmentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ExamAttemptController;
use App\Http\Controllers\AIGenerationController;

// Redirect root to appropriate page
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// Error pages
Route::get('/403', [ErrorController::class, 'forbidden'])->name('errors.403');

// Invitation routes
Route::get('/invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
Route::post('/invitation/{token}', [InvitationController::class, 'accept'])->name('invitation.accept');

// Profile setup routes (for new users)
Route::middleware('auth')->group(function () {
    Route::get('/profile/complete', [ProfileSetupController::class, 'showProfileCompletion'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileSetupController::class, 'completeProfile'])->name('profile.complete.store');
    Route::get('/profile/setup/classes', [ProfileSetupController::class, 'showClassSelection'])->name('profile.setup.classes');
    Route::post('/profile/setup/classes', [ProfileSetupController::class, 'saveClassSelection'])->name('profile.setup.classes.store');
    Route::patch('/profile/preferences', [ProfileController::class, 'updatePreferences'])->name('profile.update-preferences');
});

// Teacher routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');
});

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard - accessible to all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Users management
    Route::middleware('permission:view users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware('permission:create users')
            ->name('users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware('permission:create users')
            ->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])
            ->middleware('permission:edit users')
            ->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])
            ->middleware('permission:edit users')
            ->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:delete users')
            ->name('users.destroy');
        Route::post('/users/invite', [InvitationController::class, 'send'])
            ->middleware('permission:create users')
            ->name('users.invite');
        Route::get('/users/{teacher}/assign-classes', [TeacherAssignmentController::class, 'show'])
            ->middleware('permission:edit users')
            ->name('users.assign-classes');
        Route::put('/users/{teacher}/assign-classes', [TeacherAssignmentController::class, 'update'])
            ->middleware('permission:edit users')
            ->name('users.assign-classes.update');
    });

    // Roles management
    Route::middleware('permission:view roles')->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware('permission:create roles')
            ->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:create roles')
            ->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('permission:edit roles')
            ->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:edit roles')
            ->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:delete roles')
            ->name('roles.destroy');
    });

    // Classes management
    Route::middleware('permission:view classes')->group(function () {
        Route::get('/classes', [ClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/create', [ClassController::class, 'create'])
            ->middleware('permission:create classes')
            ->name('classes.create');
        Route::post('/classes', [ClassController::class, 'store'])
            ->middleware('permission:create classes')
            ->name('classes.store');
        Route::get('/classes/{id}/edit', [ClassController::class, 'edit'])
            ->middleware('permission:edit classes')
            ->name('classes.edit');
        Route::put('/classes/{id}', [ClassController::class, 'update'])
            ->middleware('permission:edit classes')
            ->name('classes.update');
        Route::delete('/classes/{id}', [ClassController::class, 'destroy'])
            ->middleware('permission:delete classes')
            ->name('classes.destroy');
        Route::post('/classes/import', [ClassController::class, 'import'])
            ->middleware('permission:create classes')
            ->name('classes.import');
        Route::get('/classes/template/download', [ClassController::class, 'downloadTemplate'])
            ->name('classes.template.download');
    });

    // Subjects management
    Route::middleware('permission:view subjects')->group(function () {
        Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/create', [SubjectController::class, 'create'])
            ->middleware('permission:create subjects')
            ->name('subjects.create');
        Route::post('/subjects', [SubjectController::class, 'store'])
            ->middleware('permission:create subjects')
            ->name('subjects.store');
        Route::get('/subjects/{id}/edit', [SubjectController::class, 'edit'])
            ->middleware('permission:edit subjects')
            ->name('subjects.edit');
        Route::put('/subjects/{id}', [SubjectController::class, 'update'])
            ->middleware('permission:edit subjects')
            ->name('subjects.update');
        Route::delete('/subjects/{id}', [SubjectController::class, 'destroy'])
            ->middleware('permission:delete subjects')
            ->name('subjects.destroy');
        Route::post('/subjects/import', [SubjectController::class, 'import'])
            ->middleware('permission:create subjects')
            ->name('subjects.import');
        Route::get('/subjects/template/download', [SubjectController::class, 'downloadTemplate'])
            ->name('subjects.template.download');
    });

    // Students management
    Route::middleware('permission:view students')->group(function () {
        Route::get('/students', [App\Http\Controllers\Admin\StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [App\Http\Controllers\Admin\StudentController::class, 'create'])
            ->middleware('permission:create students')
            ->name('students.create');
        Route::post('/students', [App\Http\Controllers\Admin\StudentController::class, 'store'])
            ->middleware('permission:create students')
            ->name('students.store');
        Route::get('/students/{id}/edit', [App\Http\Controllers\Admin\StudentController::class, 'edit'])
            ->middleware('permission:edit students')
            ->name('students.edit');
        Route::put('/students/{id}', [App\Http\Controllers\Admin\StudentController::class, 'update'])
            ->middleware('permission:edit students')
            ->name('students.update');
        Route::delete('/students/{id}', [App\Http\Controllers\Admin\StudentController::class, 'destroy'])
            ->middleware('permission:delete students')
            ->name('students.destroy');
        Route::post('/students/import/{classId}', [App\Http\Controllers\Admin\StudentController::class, 'import'])
            ->middleware('permission:create students')
            ->name('students.import');
        Route::get('/students/template/download', [App\Http\Controllers\Admin\StudentController::class, 'downloadTemplate'])
            ->name('students.template.download');
        Route::post('/students/{id}/assign-class/{classId}', [App\Http\Controllers\Admin\StudentController::class, 'assignToClass'])
            ->middleware('permission:edit students')
            ->name('students.assign-class');
        Route::get('/classes/{classId}/students', [App\Http\Controllers\Admin\StudentController::class, 'getByClass'])
            ->name('students.by-class');
    });

    // Exams management
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
        Route::get('/create', [ExamController::class, 'create'])->name('create');
        Route::post('/', [ExamController::class, 'store'])->name('store');
        Route::get('/{id}', [ExamController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [ExamController::class, 'edit'])->name('edit');
        Route::put('/{id}', [ExamController::class, 'update'])->name('update');
        Route::delete('/{id}', [ExamController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [ExamController::class, 'publish'])->name('publish');
        Route::post('/{id}/archive', [ExamController::class, 'archive'])->name('archive');
        Route::post('/{id}/duplicate', [ExamController::class, 'duplicate'])->name('duplicate');
        Route::get('/{id}/statistics', [ExamController::class, 'statistics'])->name('statistics');
        Route::get('/class/{classId}', [ExamController::class, 'byClass'])->name('by-class');
        Route::get('/subject/{subjectId}', [ExamController::class, 'bySubject'])->name('by-subject');

        // Questions routes
        Route::prefix('/{examId}/questions')->name('questions.')->group(function () {
            Route::get('/', [QuestionController::class, 'byExam'])->name('index');
            Route::post('/', [QuestionController::class, 'store'])->name('store');
            Route::post('/bulk', [QuestionController::class, 'storeBulk'])->name('store-bulk');
            Route::post('/reorder', [QuestionController::class, 'reorder'])->name('reorder');
            Route::post('/generate-ai', [App\Http\Controllers\AIQuestionController::class, 'generateQuestions'])->name('generate-ai');
        });
    });

    // Questions management
    Route::prefix('questions')->name('questions.')->group(function () {
        Route::get('/bank', [QuestionController::class, 'bank'])->name('bank');
        Route::get('/{id}', [QuestionController::class, 'show'])->name('show');
        Route::put('/{id}', [QuestionController::class, 'update'])->name('update');
        Route::delete('/{id}', [QuestionController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [QuestionController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/add-to-bank', [QuestionController::class, 'addToBank'])->name('add-to-bank');
        Route::delete('/{id}/remove-from-bank', [QuestionController::class, 'removeFromBank'])->name('remove-from-bank');
        Route::post('/{id}/import', [QuestionController::class, 'importToExam'])->name('import');
        Route::post('/{id}/images', [QuestionController::class, 'uploadImage'])->name('upload-image');
        Route::delete('/{questionId}/images/{imageId}', [QuestionController::class, 'deleteImage'])->name('delete-image');
    });

    // AI Generation routes
    Route::prefix('ai')->name('ai.')->group(function () {
        Route::post('/generate', [AIGenerationController::class, 'generate'])->name('generate');
        Route::post('/history/{id}/accept', [AIGenerationController::class, 'acceptQuestions'])->name('accept');
        Route::post('/history/{id}/rate', [AIGenerationController::class, 'rateGeneration'])->name('rate');
        Route::get('/statistics', [AIGenerationController::class, 'statistics'])->name('statistics');

        // Prompts management
        Route::prefix('prompts')->name('prompts.')->group(function () {
            Route::get('/', [AIGenerationController::class, 'prompts'])->name('index');
            Route::post('/', [AIGenerationController::class, 'createPrompt'])->name('store');
            Route::put('/{id}', [AIGenerationController::class, 'updatePrompt'])->name('update');
            Route::delete('/{id}', [AIGenerationController::class, 'deletePrompt'])->name('destroy');
            Route::get('/most-used', [AIGenerationController::class, 'mostUsedPrompts'])->name('most-used');
            Route::get('/highest-rated', [AIGenerationController::class, 'highestRatedPrompts'])->name('highest-rated');
        });
    });
});

// Student exam routes (separate from admin)
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    // Exam attempts
    Route::prefix('exams')->name('exams.')->group(function () {
        Route::get('/', [ExamController::class, 'index'])->name('index');
        Route::get('/{id}', [ExamController::class, 'show'])->name('show');

        Route::post('/{examId}/start', [ExamAttemptController::class, 'start'])->name('start');
        Route::get('/{examId}/attempts', [ExamAttemptController::class, 'studentAttempts'])->name('attempts');
    });

    Route::prefix('attempts')->name('attempts.')->group(function () {
        Route::post('/{attemptId}/answer', [ExamAttemptController::class, 'submitAnswer'])->name('submit-answer');
        Route::post('/{attemptId}/complete', [ExamAttemptController::class, 'complete'])->name('complete');
        Route::post('/{attemptId}/abandon', [ExamAttemptController::class, 'abandon'])->name('abandon');
        Route::get('/{attemptId}/results', [ExamAttemptController::class, 'results'])->name('results');
    });

    Route::get('/history', [ExamAttemptController::class, 'history'])->name('history');
    Route::get('/statistics', [ExamAttemptController::class, 'studentStatistics'])->name('statistics');
});

// Teacher exam attempt grading routes
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/exams/{examId}/attempts', [ExamAttemptController::class, 'examStatistics'])->name('exam-attempts');
    Route::post('/answers/{answerId}/grade', [ExamAttemptController::class, 'gradeAnswer'])->name('grade-answer');
    Route::get('/students/{studentId}/statistics', [ExamAttemptController::class, 'studentStatistics'])->name('student-statistics');
});
