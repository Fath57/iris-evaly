<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentAuthController;
use App\Http\Controllers\Api\StudentExamAttemptController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Routes publiques pour l'authentification des étudiants
Route::prefix('students')->group(function () {
    Route::post('login', [StudentAuthController::class, 'login']);
    Route::post('setup-password', [StudentAuthController::class, 'setupPassword']);
});

// Routes protégées pour les étudiants
Route::middleware('auth:sanctum')->prefix('students')->group(function () {
    Route::get('profile', [StudentAuthController::class, 'getProfile']);
    Route::put('profile', [StudentAuthController::class, 'updateProfile']);
    Route::get('exams', [StudentAuthController::class, 'getExams']);
    Route::get('exams/history', [StudentExamAttemptController::class, 'history']);
    Route::get('exams/{exam}/questions', [StudentExamAttemptController::class, 'questions']);
    Route::post('exams/{exam}/submit', [StudentExamAttemptController::class, 'submit']);
    Route::get('exams/{exam}/results', [StudentExamAttemptController::class, 'results']);
    Route::post('change-password', [StudentAuthController::class, 'changePassword']);
    Route::post('logout', [StudentAuthController::class, 'logout']);
});

// Routes pour l'authentification des étudiants (maintenues dans l'API)
Route::middleware('auth:sanctum')->group(function () {
    // Note: Les routes de gestion des étudiants ont été déplacées vers l'interface Inertia.js
    // Voir routes/web.php pour les nouvelles routes
});
