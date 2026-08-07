<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\CourseBlock;
use App\Models\AcademicYear;
use App\Models\Section; // Still needed for validation
use App\Models\Employee; // Still needed for validation
use App\Models\Course; // Still needed for validation
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CourseBlockBulkUploader extends Component
{
    use WithFileUploads;

    // --- Context Selection Properties ---
    public $academicYearId;
    public $semester;

    // --- Data for Dropdowns ---
    public $academicYears = [];
    public $semesters = ['1st', '2nd', 'Summer'];

    // --- CSV Upload State ---
    public $csvFile; 
    public $uploading = false;
    public $showUploadForm = false; // Controls visibility of upload form

    // --- Lifecycle and Initialization ---

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
    }
    
    // Updates the visibility of the upload form when context changes
    public function updated($propertyName)
    {
        if ($propertyName == 'academicYearId' || $propertyName == 'semester') {
            $this->showUploadForm = $this->academicYearId && $this->semester;
        }
    }


    // ------------------------------------------
    // --- BULK UPLOAD METHOD (Revised) ---
    // ------------------------------------------

    public function bulkUploadCourseBlocks()
    {
        // 1. Context Validation
        if (!$this->academicYearId || !$this->semester) {
            session()->flash('error', 'Please select an **Academic Year** and **Semester** before uploading.');
            return;
        }

        // 2. File Validation
        $this->validate([
            'csvFile' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $this->uploading = true;
        $path = $this->csvFile->getRealPath();
        $file = fopen($path, 'r');
        $header = fgetcsv($file); 

        // Expected headers
        $expectedHeaders = [
            'section_id', 'course_id', 'faculty_id', 'room_name', 
            'schedule_string',
        ];

        // Map column names to indexes
        $columnIndex = array_flip($header);
        
        foreach ($expectedHeaders as $expected) {
            if (!isset($columnIndex[$expected])) {
                session()->flash('error', "CSV header validation failed. Missing required column: **{$expected}**.");
                $this->uploading = false;
                $this->reset('csvFile');
                return;
            }
        }

        DB::beginTransaction();
        $bulkErrors = [];
        try {
            $createdCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $line = 1;

            while (($row = fgetcsv($file)) !== FALSE) {
                $line++;
                
                // Construct the block data
                $data = [
                    'course_id' => $row[$columnIndex['course_id']] ?? null,
                    'faculty_id' => $row[$columnIndex['faculty_id']] ?? null,
                    'room_name' => $row[$columnIndex['room_name']] ?? null,
                    'schedule_string' => $row[$columnIndex['schedule_string']] ?? null,
                    'academic_year_id' => $this->academicYearId, // Context
                    'semester' => $this->semester, // Context
                    'finalized' => 0, // Default value
                ];
                $csvSectionId = $row[$columnIndex['section_id']] ?? null;

                // --- Row-level Validation ---
                $validator = Validator::make($data, [
                    // Ensure the IDs exist in their respective tables
                    'course_id' => 'required|exists:courses,id',
                    'faculty_id' => 'required|exists:employees,id',
                    'academic_year_id' => 'required|exists:academic_years,id',
                    'semester' => 'required|in:1st,2nd,Summer',
                    'room_name' => 'required|string|max:100',
                    'schedule_string' => 'required|string|max:150',
                ] + ['section_id' => 'required|exists:sections,id']);

                if ($validator->fails()) {
                    $bulkErrors[] = "Line {$line}: Validation Failed. Section ID: {$csvSectionId}. " . json_encode($validator->errors()->all());
                    $skippedCount++;
                    continue;
                }

                // --- Duplicate Check / Update ---
                $existingBlock = CourseBlock::whereHas('sections', function ($q) use ($csvSectionId, $data) {
                                            $q->where('sections.id', $csvSectionId)
                                              ->where('course_block_section.academic_year_id', $data['academic_year_id'])
                                              ->where('course_block_section.semester', $data['semester']);
                                        })
                                            ->where('course_id', $data['course_id'])
                                            ->where('academic_year_id', $data['academic_year_id'])
                                            ->where('semester', $data['semester'])
                                            ->first();

                if ($existingBlock) {
                    $existingBlock->update([
                        'faculty_id' => $data['faculty_id'],
                        'room_name' => $data['room_name'],
                        'schedule_string' => $data['schedule_string'],
                        'finalized' => $data['finalized'],
                    ]);

                    if (! $existingBlock->sections()->wherePivot('section_id', $csvSectionId)->exists()) {
                        $existingBlock->sections()->attach($csvSectionId, [
                            'academic_year_id' => $data['academic_year_id'],
                            'semester' => $data['semester'],
                        ]);
                    }
                    $updatedCount++;
                } else {
                    $block = CourseBlock::create($data);
                    $block->sections()->attach($csvSectionId, [
                        'academic_year_id' => $data['academic_year_id'],
                        'semester' => $data['semester'],
                    ]);
                    $createdCount++;
                }

            } 

            DB::commit();

            $total = $createdCount + $updatedCount + $skippedCount;
            $successMessage = "Bulk Upload Complete! Total Rows: **{$total}**. Created: **{$createdCount}**, Updated: **{$updatedCount}**, Skipped (Invalid/Error): **{$skippedCount}**.";
            
            session()->flash('message', $successMessage);
            if (!empty($bulkErrors)) {
                 session()->flash('bulk_errors', $bulkErrors);
            }

            $this->reset('csvFile');
            

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Bulk upload failed: ' . $e->getMessage());
        } finally {
            $this->uploading = false;
        }
    }
    
    /**
     * Renders the corresponding Livewire view.
     */
    public function render()
    {
        $ay_name = $this->academicYearId ? (AcademicYear::find($this->academicYearId)->start_year ?? 'N/A') : 'N/A';
        
        return view('livewire.course-block-bulk-uploader', [
            'ay_name' => $ay_name
        ])
            ->extends('layouts.admin')
            ->section('content');
    }
}