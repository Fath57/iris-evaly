<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SubjectService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    protected SubjectService $subjectService;

    public function __construct(SubjectService $subjectService)
    {
        $this->subjectService = $subjectService;
    }

    public function index(Request $request)
    {
        $filters = [
            'search' => $request->input('search'),
            'is_active' => $request->input('is_active'),
            'sort_by' => $request->input('sort_by', 'name'),
            'sort_order' => $request->input('sort_order', 'asc'),
        ];

        $subjects = $this->subjectService->getSubjectsPaginated(15, $filters);

        return Inertia::render('Admin/Subjects/Index', [
            'subjects' => $subjects,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/Subjects/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $this->subjectService->createSubject($validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Matière créée avec succès');
    }

    public function edit($id)
    {
        $subject = \App\Models\Subject::findOrFail($id);

        return Inertia::render('Admin/Subjects/Edit', [
            'subject' => $subject,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:subjects,code,' . $id,
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        $this->subjectService->updateSubject($id, $validated);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Matière mise à jour avec succès');
    }

    public function destroy($id)
    {
        $this->subjectService->deleteSubject($id);

        return redirect()->route('admin.subjects.index')
            ->with('success', 'Matière supprimée avec succès');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $this->subjectService->importSubjects($request->file('file'));

            return back()->with('success', 'Matières importées avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'importation : ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        // Create a sample Excel file with headers
        $headers = [
            ['name', 'code', 'description', 'color', 'is_active'],
            ['Mathématiques', 'MATH', 'Cours de mathématiques', '#3B82F6', '1'],
            ['Physique', 'PHYS', 'Cours de physique', '#10B981', '1'],
        ];

        return response()->streamDownload(function () use ($headers) {
            $handle = fopen('php://output', 'w');
            foreach ($headers as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, 'template_matieres.csv');
    }
}
