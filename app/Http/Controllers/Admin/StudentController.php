<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StudentService;
use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ClassModel;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function __construct(
        private StudentService $studentService
    ) {
        $this->middleware('permission:view students')->only(['index', 'getByClass']);
        $this->middleware('permission:create students')->only(['create', 'store']);
        $this->middleware('permission:edit students')->only(['edit', 'update']);
        $this->middleware('permission:delete students')->only(['destroy']);
        $this->middleware('permission:import students')->only(['import', 'downloadTemplate']);
        $this->middleware('permission:assign students to class')->only(['assignToClass']);
    }

    /**
     * Display a listing of students.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['search', 'class_id', 'is_active']);
        $perPage = $request->get('per_page', 15);

        $students = $this->studentService->getStudents($filters, $perPage);
        $classes = ClassModel::all();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create()
    {
        $classes = ClassModel::all();

        return Inertia::render('Admin/Students/Create', [
            'classes' => $classes,
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $classId = $request->input('class_id');
        $data = $request->except('class_id');

        if ($classId) {
            $result = $this->studentService->createStudentWithClass($data, $classId);
        } else {
            $result = $this->studentService->createStudent($data);
        }

        if (!$result['success']) {
            return back()->withErrors($result['errors'])->withInput();
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Étudiant créé avec succès.');
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit($id)
    {
        $student = $this->studentService->getStudent($id);

        if (!$student) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Étudiant non trouvé.');
        }

        $classes = ClassModel::all();
        $studentClasses = $student->classes;

        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
            'classes' => $classes,
            'studentClasses' => $studentClasses,
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, $id)
    {
        $result = $this->studentService->updateStudent($id, $request->all());

        if (!$result['success']) {
            return back()->withErrors($result['errors'])->withInput();
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'Étudiant mis à jour avec succès.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy($id)
    {
        $student = $this->studentService->getStudent($id);

        if (!$student) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Étudiant non trouvé.');
        }

        $student->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Étudiant supprimé avec succès.');
    }

    /**
     * Import students from Excel file.
     */
    public function import(Request $request, $classId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            $import = new StudentsImport($classId);
            Excel::import($import, $request->file('file'));

            $studentsData = $import->getStudents();

            if (empty($studentsData)) {
                return back()->with('error', 'Aucune donnée valide trouvée dans le fichier.');
            }

            $result = $this->studentService->importStudents($studentsData, $classId);

            if (!$result['success']) {
                return back()->with('error', 'Erreur lors de l\'import des étudiants.');
            }

            return redirect()->route('admin.students.by-class', $classId)
                ->with('success', 'Import réalisé avec succès. ' . count($result['data']) . ' étudiants importés.');

        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import: ' . $e->getMessage());
        }
    }

    /**
     * Download import template.
     */
    public function downloadTemplate()
    {
        return Excel::download(
            new \App\Exports\StudentsTemplateExport(),
            'modele_import_etudiants.xlsx'
        );
    }

    /**
     * Assign a student to a class.
     */
    public function assignToClass($id, $classId)
    {
        $result = $this->studentService->assignToClass($id, $classId);

        if (!$result['success']) {
            return back()->with('error', $result['message'] ?? 'Erreur lors de l\'assignation de l\'étudiant à la classe.');
        }

        return back()->with('success', 'Étudiant assigné à la classe avec succès.');
    }

    /**
     * Get students by class.
     */
    public function getByClass($classId)
    {
        $students = $this->studentService->getStudentsByClass($classId);
        $class = ClassModel::find($classId);

        if (!$class) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'Classe non trouvée.');
        }

        return Inertia::render('Admin/Students/ByClass', [
            'students' => $students,
            'class' => $class,
        ]);
    }
}
