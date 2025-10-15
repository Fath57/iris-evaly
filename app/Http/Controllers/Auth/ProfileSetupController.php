<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ProfileSetupController extends Controller
{
    /**
     * Show class selection for teachers
     */
    public function showClassSelection()
    {
        $user = auth()->user();

        // Only teachers need to select classes
        if (!$user->hasRole('teacher')) {
            return redirect()->route('admin.dashboard');
        }

        $classes = ClassModel::where('is_active', true)
            ->with('subjects')
            ->get();

        $subjects = DB::table('subjects')
            ->where('is_active', true)
            ->get();

        return Inertia::render('Auth/SelectClasses', [
            'classes' => $classes,
            'subjects' => $subjects,
        ]);
    }

    /**
     * Save teacher's class selections
     */
    public function saveClassSelection(Request $request)
    {
        $user = auth()->user();

        if (!$user->hasRole('teacher')) {
            return redirect()->route('admin.dashboard');
        }

        $validated = $request->validate([
            'selections' => 'required|array',
            'selections.*.class_id' => 'required|exists:classes,id',
            'selections.*.subject_id' => 'nullable|exists:subjects,id',
            'selections.*.is_main_teacher' => 'boolean',
        ]);

        // Clear existing selections
        DB::table('teacher_classes')->where('teacher_id', $user->id)->delete();

        // Save new selections
        foreach ($validated['selections'] as $selection) {
            DB::table('teacher_classes')->insert([
                'teacher_id' => $user->id,
                'class_id' => $selection['class_id'],
                'subject_id' => $selection['subject_id'] ?? null,
                'is_main_teacher' => $selection['is_main_teacher'] ?? false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Vos classes ont été configurées avec succès !');
    }

    /**
     * Show profile completion form
     */
    public function showProfileCompletion()
    {
        $user = auth()->user();

        if (!$user->needsProfileCompletion()) {
            return redirect()->route('admin.dashboard');
        }

        return Inertia::render('Auth/CompleteProfile', [
            'user' => [
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Complete user profile
     */
    public function completeProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => $validated['first_name'] . ' ' . $validated['last_name'],
            'profile_completed' => true,
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = bcrypt($validated['password']);
        }

        $user->update($updateData);

        // If teacher, redirect to class selection
        if ($user->hasRole('teacher')) {
            return redirect()->route('profile.setup.classes')
                ->with('success', 'Profil complété ! Veuillez maintenant sélectionner vos classes.');
        }

        return redirect()->route('admin.dashboard')
            ->with('success', 'Profil complété avec succès !');
    }
}