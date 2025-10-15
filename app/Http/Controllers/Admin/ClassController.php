<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ClassService;
use App\Imports\ClassesImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;

class ClassController extends Controller
{
    protected ClassService $classService;

    public function __construct(ClassService $classService)
    {
        $this->classService = $classService;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'level' => $request->input('level'),
            'is_active' => $request->input('is_active'),
            'sort_by' => $request->input('sort_by', 'created_at'),
            'sort_order' => $request->input('sort_order', 'desc'),
        ];

        $classes = $this->classService->getClassesPaginated(15, $filters);

        // Add additional data for each class
        $classes->getCollection()->transform(function ($class) {
            $class->students_count = DB::table('class_student')->where('class_id', $class->id)->count();
            $class->subjects_count = DB::table('class_subject')->where('class_id', $class->id)->count();
            $class->teachers = DB::table('teacher_classes')
                ->join('users', 'teacher_classes.teacher_id', '=', 'users.id')
                ->where('teacher_classes.class_id', $class->id)
                ->select('users.name', 'teacher_classes.is_main_teacher')
                ->get();
            $class->main_teacher_name = $class->teachers->where('is_main_teacher', true)->first()->name ?? null;
            return $class;
        });

        return Inertia::render('Admin/Classes/Index', [
            'classes' => $classes,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $teachers = User::role('teacher')->get(['id', 'name', 'email']);
        $students = User::role('student')->get(['id', 'name', 'email']);
        $subjects = DB::table('subjects')->where('is_active', true)->get();
        
        return Inertia::render('Admin/Classes/Create', [
            'teachers' => $teachers,
            'students' => $students,
            'subjects' => $subjects,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code',
            'description' => 'nullable|string',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'level' => 'required|in:primaire,college,lycee,universite',
            'is_active' => 'boolean',
            'teachers' => 'nullable|array',
            'students' => 'nullable|array',
        ]);

        $class = $this->classService->createClass([
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'academic_year' => $validated['academic_year'],
            'level' => $validated['level'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach teachers and students
        if (!empty($validated['teachers'])) {
            $this->classService->manageTeachers($class->id, $validated['teachers']);
        }

        if (!empty($validated['students'])) {
            $this->classService->manageStudents($class->id, $validated['students']);
        }

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe créée avec succès');
    }

    public function edit($id)
    {
        $class = $this->classService->getClassWithRelations($id);
        $teachers = User::role('teacher')->get(['id', 'name', 'email']);
        $students = User::role('student')->get(['id', 'name', 'email']);
        $subjects = DB::table('subjects')->where('is_active', true)->get();
        
        $enrolledStudents = $class->students->pluck('id')->toArray();
        $assignedTeachers = DB::table('teacher_classes')
            ->where('class_id', $id)
            ->get()
            ->toArray();

        return Inertia::render('Admin/Classes/Edit', [
            'class' => $class,
            'teachers' => $teachers,
            'students' => $students,
            'subjects' => $subjects,
            'enrolledStudents' => $enrolledStudents,
            'assignedTeachers' => $assignedTeachers,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:classes,code,' . $id,
            'description' => 'nullable|string',
            'academic_year' => 'required|integer|min:2000|max:2100',
            'level' => 'required|in:primaire,college,lycee,universite',
            'is_active' => 'boolean',
            'teachers' => 'nullable|array',
            'students' => 'nullable|array',
        ]);

        $this->classService->updateClass($id, [
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'academic_year' => $validated['academic_year'],
            'level' => $validated['level'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Update teachers and students
        if (isset($validated['teachers'])) {
            $this->classService->manageTeachers($id, $validated['teachers']);
        }

        if (isset($validated['students'])) {
            $this->classService->manageStudents($id, $validated['students']);
        }

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe mise à jour avec succès');
    }

    public function destroy($id)
    {
        $this->classService->deleteClass($id);

        return redirect()->route('admin.classes.index')
            ->with('success', 'Classe supprimée avec succès');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            Excel::import(new ClassesImport, $request->file('file'));

            return back()->with('success', 'Classes importées avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            ['name', 'code', 'description', 'academic_year', 'level', 'is_active'],
            ['Terminale S - Groupe A', 'TS-A', 'Classe de Terminale Scientifique', '2024', 'lycee', '1'],
            ['Première S', '1S', 'Classe de Première Scientifique', '2024', 'lycee', '1'],
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            foreach ($headers as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'template_classes.csv');
    }
}
