<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Redirect teachers to their specific dashboard
        if (auth()->user()->hasRole('teacher') && !auth()->user()->hasAnyRole(['admin', 'super-admin'])) {
            return redirect()->route('teacher.dashboard');
        }

        $stats = [
            'total_users' => User::count(),
            'total_students' => User::role('student')->count(),
            'total_teachers' => User::role('teacher')->count(),
            'total_classes' => DB::table('classes')->where('is_active', true)->count(),
            'total_subjects' => DB::table('subjects')->where('is_active', true)->count(),
            'total_exams' => DB::table('exams')->count(),
            'ongoing_exams' => DB::table('exams')->where('status', 'ongoing')->count(),
            'completed_exams' => DB::table('exams')->where('status', 'completed')->count(),
        ];

        // Recent activities
        $recentUsers = User::latest()->take(5)->get();
        $recentExams = DB::table('exams')
            ->join('subjects', 'exams.subject_id', '=', 'subjects.id')
            ->join('classes', 'exams.class_id', '=', 'classes.id')
            ->select('exams.*', 'subjects.name as subject_name', 'classes.name as class_name')
            ->latest('exams.created_at')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentExams' => $recentExams,
        ]);
    }
}
