<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class TeacherDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Get teacher's classes
        $teacherClasses = DB::table('teacher_classes')
            ->join('classes', 'teacher_classes.class_id', '=', 'classes.id')
            ->leftJoin('subjects', 'teacher_classes.subject_id', '=', 'subjects.id')
            ->where('teacher_classes.teacher_id', $user->id)
            ->select(
                'classes.*',
                'teacher_classes.is_main_teacher',
                'subjects.name as subject_name',
                'subjects.color as subject_color'
            )
            ->get();

        // Get class IDs
        $classIds = $teacherClasses->pluck('id')->unique()->toArray();

        // Get teacher's exams
        $teacherExams = DB::table('exams')
            ->join('classes', 'exams.class_id', '=', 'classes.id')
            ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->where('exams.created_by', $user->id)
            ->select(
                'exams.*',
                'classes.name as class_name',
                'subjects.name as subject_name'
            )
            ->latest('exams.created_at')
            ->take(5)
            ->get();

        // Get students count in teacher's classes
        $studentsCount = DB::table('class_student')
            ->whereIn('class_id', $classIds)
            ->distinct('student_id')
            ->count('student_id');

        // Get exams statistics
        $examsStats = [
            'total' => DB::table('exams')->where('created_by', $user->id)->count(),
            'draft' => DB::table('exams')->where('created_by', $user->id)->where('status', 'draft')->count(),
            'published' => DB::table('exams')->where('created_by', $user->id)->where('status', 'published')->count(),
            'ongoing' => DB::table('exams')->where('created_by', $user->id)->where('status', 'ongoing')->count(),
            'completed' => DB::table('exams')->where('created_by', $user->id)->where('status', 'completed')->count(),
        ];

        // Get upcoming exams
        $upcomingExams = DB::table('exams')
            ->join('classes', 'exams.class_id', '=', 'classes.id')
            ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->where('exams.created_by', $user->id)
            ->where('exams.start_date', '>', now())
            ->where('exams.status', '!=', 'archived')
            ->select(
                'exams.*',
                'classes.name as class_name',
                'subjects.name as subject_name'
            )
            ->orderBy('exams.start_date')
            ->take(5)
            ->get();

        // Group classes by main teacher status
        $mainClasses = $teacherClasses->where('is_main_teacher', true);
        $otherClasses = $teacherClasses->where('is_main_teacher', false);

        return Inertia::render('Teacher/Dashboard', [
            'stats' => [
                'total_classes' => $teacherClasses->count(),
                'main_classes' => $mainClasses->count(),
                'total_students' => $studentsCount,
                'total_exams' => $examsStats['total'],
                'draft_exams' => $examsStats['draft'],
                'published_exams' => $examsStats['published'],
                'ongoing_exams' => $examsStats['ongoing'],
                'completed_exams' => $examsStats['completed'],
            ],
            'classes' => $teacherClasses,
            'mainClasses' => $mainClasses,
            'recentExams' => $teacherExams,
            'upcomingExams' => $upcomingExams,
        ]);
    }
}
