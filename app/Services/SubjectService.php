<?php

namespace App\Services;

use App\Repositories\SubjectRepository;
use App\Imports\SubjectsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class SubjectService
{
    protected SubjectRepository $subjectRepository;

    public function __construct(SubjectRepository $subjectRepository)
    {
        $this->subjectRepository = $subjectRepository;
    }

    public function getAllSubjects()
    {
        return $this->subjectRepository->all();
    }

    public function getActiveSubjects()
    {
        return $this->subjectRepository->getActiveSubjects();
    }

    public function getSubjectsPaginated(int $perPage = 15, array $filters = [])
    {
        return $this->subjectRepository->paginate($perPage, $filters);
    }

    public function createSubject(array $data)
    {
        try {
            $subject = $this->subjectRepository->create($data);

            Log::info('Subject created', ['subject_id' => $subject->id, 'name' => $subject->name]);

            return $subject;
        } catch (\Exception $e) {
            Log::error('Error creating subject', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function updateSubject(int $id, array $data)
    {
        try {
            $this->subjectRepository->update($id, $data);

            Log::info('Subject updated', ['subject_id' => $id]);

            return $this->subjectRepository->find($id);
        } catch (\Exception $e) {
            Log::error('Error updating subject', ['subject_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteSubject(int $id)
    {
        try {
            $result = $this->subjectRepository->delete($id);
            
            if ($result) {
                Log::info('Subject deleted', ['subject_id' => $id]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Error deleting subject', ['subject_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function importSubjects($file)
    {
        try {
            Excel::import(new SubjectsImport, $file);
            
            Log::info('Subjects imported successfully');
            
            return true;
        } catch (\Exception $e) {
            Log::error('Error importing subjects', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function exportTemplate()
    {
        // TODO: Implement export template
    }
}







