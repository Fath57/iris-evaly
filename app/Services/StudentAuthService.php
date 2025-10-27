<?php

namespace App\Services;

use App\Interfaces\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class StudentAuthService
{
    public function __construct(
        private StudentRepositoryInterface $studentRepository
    ) {}

    /**
     * Authenticate student
     */
    public function login(array $credentials): array
    {
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        $student = $this->studentRepository->findByEmail($credentials['email']);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ];
        }

        if (!$student->is_active) {
            return [
                'success' => false,
                'message' => 'Compte étudiant désactivé.'
            ];
        }

        if (!$student->hasPasswordSet()) {
            return [
                'success' => false,
                'message' => 'Vous devez d\'abord définir votre mot de passe.',
                'requires_password_setup' => true,
                'student_id' => $student->id
            ];
        }

        if (!Hash::check($credentials['password'], $student->password)) {
            return [
                'success' => false,
                'message' => 'Mot de passe incorrect.'
            ];
        }

        // Create token for API authentication
        $token = $student->createToken('student-token')->plainTextToken;

        return [
            'success' => true,
            'data' => [
                'student' => $student->load('classes'),
                'token' => $token
            ],
            'message' => 'Connexion réussie.'
        ];
    }

    /**
     * Setup initial password for student using email
     */
    public function setupPassword(string $email, string $password, string $confirmPassword): array
    {
        $validator = Validator::make([
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $confirmPassword
        ], [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        $student = $this->studentRepository->findByEmail($email);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé avec cet email.'
            ];
        }

        if (!$student->is_active) {
            return [
                'success' => false,
                'message' => 'Compte étudiant désactivé.'
            ];
        }

        if ($student->hasPasswordSet()) {
            return [
                'success' => false,
                'message' => 'Le mot de passe a déjà été défini. Utilisez la fonctionnalité de connexion.'
            ];
        }

        $success = $this->studentRepository->updatePassword($student->id, $password);

        if ($success) {
            // Auto-login after password setup
            $token = $student->createToken('student-token')->plainTextToken;

            return [
                'success' => true,
                'data' => [
                    'student' => $student->fresh()->load('classes'),
                    'token' => $token
                ],
                'message' => 'Mot de passe défini avec succès. Vous êtes maintenant connecté.'
            ];
        }

        return [
            'success' => false,
            'message' => 'Erreur lors de la définition du mot de passe.'
        ];
    }

    /**
     * Change student password
     */
    public function changePassword(int $studentId, string $currentPassword, string $newPassword, string $confirmPassword): array
    {
        $validator = Validator::make([
            'current_password' => $currentPassword,
            'password' => $newPassword,
            'password_confirmation' => $confirmPassword
        ], [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        $student = $this->studentRepository->getById($studentId);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ];
        }

        if (!Hash::check($currentPassword, $student->password)) {
            return [
                'success' => false,
                'message' => 'Mot de passe actuel incorrect.'
            ];
        }

        $success = $this->studentRepository->updatePassword($studentId, $newPassword);

        return [
            'success' => $success,
            'message' => $success ? 'Mot de passe modifié avec succès.' : 'Erreur lors de la modification du mot de passe.'
        ];
    }

    /**
     * Get authenticated student profile
     */
    public function getProfile(int $studentId): array
    {
        $student = $this->studentRepository->getById($studentId);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ];
        }

        return [
            'success' => true,
            'data' => $student->load('classes')
        ];
    }

    /**
     * Update student profile
     */
    public function updateProfile(int $studentId, array $data): array
    {
        $validator = Validator::make($data, [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'date_of_birth' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()
            ];
        }

        $student = $this->studentRepository->getById($studentId);

        if (!$student) {
            return [
                'success' => false,
                'message' => 'Étudiant non trouvé.'
            ];
        }

        // Remove sensitive fields that shouldn't be updated via profile
        unset($data['email'], $data['student_number'], $data['password'], $data['is_active']);

        $success = $this->studentRepository->update($studentId, $data);

        return [
            'success' => $success,
            'data' => $success ? $this->studentRepository->getById($studentId)->load('classes') : null,
            'message' => $success ? 'Profil mis à jour avec succès.' : 'Erreur lors de la mise à jour du profil.'
        ];
    }

    /**
     * Logout student (revoke tokens)
     */
    public function logout(Student $student): array
    {
        $student->tokens()->delete();

        return [
            'success' => true,
            'message' => 'Déconnexion réussie.'
        ];
    }
}
