<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeacherAssignmentController extends Controller
{
    /**
     * Show teacher assignment form
     */
    public function show($teacherId)
    {
        $teacher = User::with('teachingClasses')->findOrFail($teacherId);

        // Verify user is a teacher
        if (!$teacher->hasRole('teacher')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cet utilisateur n\'est pas un professeur.');
        }

        $classes = ClassModel::where('is_active', true)->get();
        $subjects = DB::table('subjects')->where('is_active', true)->get();

        // Get current assignments
        $currentAssignments = DB::table('teacher_classes')
            ->where('teacher_id', $teacherId)
            ->get()
            ->map(function ($assignment) {
                return [
                    'class_id' => $assignment->class_id,
                    'subject_id' => $assignment->subject_id,
                    'is_main_teacher' => (bool) $assignment->is_main_teacher,
                ];
            })
            ->toArray();

        return Inertia::render('Admin/Teachers/AssignClasses', [
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->name,
                'email' => $teacher->email,
            ],
            'classes' => $classes,
            'subjects' => $subjects,
            'currentAssignments' => $currentAssignments,
        ]);
    }

    /**
     * Update teacher class assignments
     */
    public function update(Request $request, $teacherId)
    {
        $teacher = User::findOrFail($teacherId);

        if (!$teacher->hasRole('teacher')) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Cet utilisateur n\'est pas un professeur.');
        }

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.class_id' => 'required|exists:classes,id',
            'assignments.*.subject_id' => 'nullable|exists:subjects,id',
            'assignments.*.is_main_teacher' => 'boolean',
        ]);

        try {
            // Clear existing assignments
            DB::table('teacher_classes')->where('teacher_id', $teacherId)->delete();

            // Create new assignments
            foreach ($validated['assignments'] as $assignment) {
                DB::table('teacher_classes')->insert([
                    'teacher_id' => $teacherId,
                    'class_id' => $assignment['class_id'],
                    'subject_id' => $assignment['subject_id'] ?? null,
                    'is_main_teacher' => $assignment['is_main_teacher'] ?? false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return redirect()->route('admin.users.index')
                ->with('success', 'Classes assignées avec succès au professeur.');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'assignation des classes.');
        }
    }
}
