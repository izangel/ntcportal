<?php

namespace App\Http\Controllers;

use App\Models\ClearanceShs;
use App\Models\ClearanceCollege;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentClearanceController extends Controller
{
    /**
     * Map department names to database column names and related metadata
     */
    private function getDepartmentColumnMapping(): array
    {
        return [
            'Registrar\'s Office' => ['column' => 'registrar_status', 'approved_by' => 'registrar_approved_by', 'approved_at' => 'registrar_approved_at', 'remarks' => 'registrar_remarks', 'shs' => true, 'college' => true],
            'Guidance Office' => ['column' => 'guidance_status', 'approved_by' => 'guidance_approved_by', 'approved_at' => 'guidance_approved_at', 'remarks' => 'guidance_remarks', 'shs' => true, 'college' => true],
            'SHS Adviser' => ['column' => 'adviser_status', 'approved_by' => 'adviser_approved_by', 'approved_at' => 'adviser_approved_at', 'remarks' => 'adviser_remarks', 'shs' => true, 'college' => false],
            'Student\'s Accounts Office' => ['column' => 'sao_status', 'approved_by' => 'sao_approved_by', 'approved_at' => 'sao_approved_at', 'remarks' => 'sao_remarks', 'shs' => true, 'college' => true],
            'Laboratory In-charge' => ['column' => 'lab_status', 'approved_by' => 'lab_approved_by', 'approved_at' => 'lab_approved_at', 'remarks' => 'lab_remarks', 'shs' => true, 'college' => true],
            'SHS Organization' => ['column' => 'org_status', 'approved_by' => 'org_approved_by', 'approved_at' => 'org_approved_at', 'remarks' => 'org_remarks', 'shs' => true, 'college' => false],
            'Supreme Student Government (SSG)' => ['column' => 'ssg_status', 'approved_by' => 'ssg_approved_by', 'approved_at' => 'ssg_approved_at', 'remarks' => 'ssg_remarks', 'shs' => true, 'college' => false],
            'Supreme Student Council (SSC)' => ['column' => 'ssc_status', 'approved_by' => 'ssc_approved_by', 'approved_at' => 'ssc_approved_at', 'remarks' => 'ssc_remarks', 'shs' => false, 'college' => true],
            'Librarian' => ['column' => 'librarian_status', 'approved_by' => 'librarian_approved_by', 'approved_at' => 'librarian_approved_at', 'remarks' => 'librarian_remarks', 'shs' => true, 'college' => true],
            'Prefect of Discipline' => ['column' => 'pod_status', 'approved_by' => 'pod_approved_by', 'approved_at' => 'pod_approved_at', 'remarks' => 'pod_remarks', 'shs' => true, 'college' => true],
            'SHS Coordinator' => ['column' => 'coordinator_status', 'approved_by' => 'coordinator_approved_by', 'approved_at' => 'coordinator_approved_at', 'remarks' => 'coordinator_remarks', 'shs' => true, 'college' => false],
            'Director for Student Affairs and Services' => ['column' => 'dsas_status', 'approved_by' => 'dsas_approved_by', 'approved_at' => 'dsas_approved_at', 'remarks' => 'dsas_remarks', 'shs' => true, 'college' => true],
            'Director of Academic Affairs' => ['column' => 'ah_status', 'approved_by' => 'ah_approved_by', 'approved_at' => 'ah_approved_at', 'remarks' => 'ah_remarks', 'shs' => true, 'college' => true],
            'School Administrator' => ['column' => 'admin_status', 'approved_by' => 'admin_approved_by', 'approved_at' => 'admin_approved_at', 'remarks' => 'admin_remarks', 'shs' => true, 'college' => true],
        ];
    }

    /**
     * Show the clearance page for students.
     */
    private function loadEmployeeClearanceData(): array
    {
        $user = Auth::user();
        $employee = $user->employee;
        $deptOffice = $employee?->deptOffice;
        
        $query = Student::with(['user', 'clearanceShs', 'clearanceCollege']);
        
        // Filter students based on employee's department
        if ($deptOffice) {
            $deptName = trim($deptOffice->name);
            $columnMapping = $this->getDepartmentColumnMapping();
            
            if (isset($columnMapping[$deptName])) {
                $mapping = $columnMapping[$deptName];
                $statusColumn = $mapping['column'];

                $query->where(function ($q) use ($mapping, $statusColumn) {
                    $hasCondition = false;

                    if ($mapping['shs']) {
                        $hasCondition = true;
                        $q->whereHas('clearanceShs', function ($subq) use ($statusColumn) {
                            $subq->where($statusColumn, 'pending');
                        });
                    }

                    if ($mapping['college']) {
                        if ($hasCondition) {
                            $q->orWhereHas('clearanceCollege', function ($subq) use ($statusColumn) {
                                $subq->where($statusColumn, 'pending');
                            });
                        } else {
                            $q->whereHas('clearanceCollege', function ($subq) use ($statusColumn) {
                                $subq->where($statusColumn, 'pending');
                            });
                        }
                    }
                });
            }
        }
        
        $students = $query->orderBy('last_name')->get();

        return [
            'employeeDeptOffice' => $deptOffice,
            'students' => $students,
            'isEmployeeView' => true,
        ];
    }

    private function calculateClearanceProgress(Student $student, bool $isShs, bool $isCollege): array
    {
        $columnMapping = $this->getDepartmentColumnMapping();
        $shsOnlyDepartments = ['SHS Adviser', 'SHS Organization', 'Supreme Student Government (SSG)', 'SHS Coordinator'];
        $collegeOnlyDepartments = ['Supreme Student Council (SSC)'];

        $total = 0;
        $approved = 0;

        foreach ($columnMapping as $department => $mapping) {
            $shouldShowAsNotApplicable = ($isShs && in_array($department, $collegeOnlyDepartments, true)) || ($isCollege && in_array($department, $shsOnlyDepartments, true));

            if ($shouldShowAsNotApplicable) {
                continue;
            }

            $total++;
            $statusValue = null;

            if ($isShs && $mapping['shs'] && $student->clearanceShs) {
                $statusValue = $student->clearanceShs->{$mapping['column']};
            }

            if (!$statusValue && $isCollege && $mapping['college'] && $student->clearanceCollege) {
                $statusValue = $student->clearanceCollege->{$mapping['column']};
            }

            if ($statusValue === 'approved') {
                $approved++;
            }
        }

        return [
            'approved' => $approved,
            'total' => $total,
        ];
    }

    public function index()
    {
        if (Auth::user()->hasAnyRole(['teacher', 'staff', 'academic_head', 'hr', 'admin'])) {
            return view('student.clearance.index', $this->loadEmployeeClearanceData());
        }

        $student = Auth::user()->student;
        abort_unless($student !== null, 403);

        // Determine student's classification
        $student->load(['clearanceShs', 'clearanceCollege']);
        $isShs = $student->clearanceShs !== null;
        $isCollege = $student->clearanceCollege !== null;

        $progress = $this->calculateClearanceProgress($student, $isShs, $isCollege);
        $progressPercentage = $progress['total'] > 0 ? round($progress['approved'] / $progress['total'] * 100) : 0;

        return view('student.clearance.index', [
            'employeeDeptOffice' => null,
            'isEmployeeView' => false,
            'student' => $student,
            'isShs' => $isShs,
            'isCollege' => $isCollege,
            'columnMapping' => $this->getDepartmentColumnMapping(),
            'clearanceApprovedCount' => $progress['approved'],
            'clearanceTotalCount' => $progress['total'],
            'clearanceProgressPercentage' => $progressPercentage,
        ]);
    }

    /**
     * Show the clearance requirements page for students.
     */
    public function requirements()
    {
        if (Auth::user()->hasAnyRole(['teacher', 'staff', 'academic_head', 'hr', 'admin'])) {
            return view('student.clearance.requirements', $this->loadEmployeeClearanceData());
        }

        $student = Auth::user()->student;
        abort_unless($student !== null, 403);

        return view('student.clearance.requirements', [
            'employeeDeptOffice' => null,
            'isEmployeeView' => false,
        ]);
    }

    /**
     * Sign for clearance - set status to pending
     */
    public function signForClearance(Request $request, $department)
    {
        $student = Auth::user()->student;
        abort_unless($student !== null, 403);

        // Decode the department name (from URL encoding)
        $departmentName = trim(urldecode($department));
        $columnMapping = $this->getDepartmentColumnMapping();
        
        abort_unless(isset($columnMapping[$departmentName]), 400);
        
        $mapping = $columnMapping[$departmentName];
        $statusColumn = $mapping['column'];
        
        // Load student's clearance records
        $student->load(['clearanceShs', 'clearanceCollege']);
        
        if ($student->clearanceShs) {
            if ($mapping['shs']) {
                ClearanceShs::where('student_id', $student->id)->update([
                    $statusColumn => 'pending'
                ]);
            }
        }
        
        if ($student->clearanceCollege) {
            if ($mapping['college']) {
                ClearanceCollege::where('student_id', $student->id)->update([
                    $statusColumn => 'pending'
                ]);
            }
        }
        
        return redirect()
            ->route('student.clearance.index')
            ->with('status', "Submitted clearance for {$departmentName}.");
    }

    public function classifyGraduating(Request $request)
    {
        abort_unless(Auth::user()->hasAnyRole(['teacher', 'staff', 'academic_head', 'hr', 'admin']), 403);
        $employee = Auth::user()->employee;
        abort_unless($employee && $employee->deptOffice?->name === "Registrar's Office", 403);

        $query = Student::query();

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($query) use ($search) {
                $query->where('student_id', 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                    ->orWhere(DB::raw("CONCAT(last_name, ', ', first_name)"), 'like', "%{$search}%");
            });
        }

        // Filter by classification
        if ($request->filled('classification_filter')) {
            $classificationFilter = $request->get('classification_filter');
            if ($classificationFilter === 'shs') {
                $query->whereHas('clearanceShs');
            } elseif ($classificationFilter === 'college') {
                $query->whereHas('clearanceCollege');
            } elseif ($classificationFilter === 'unclassified') {
                $query->whereDoesntHave('clearanceShs')->whereDoesntHave('clearanceCollege');
            }
        }

        $sortBy = $request->get('sort_by', 'last_name');
        $sortDirection = $request->get('sort_direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'student_id') {
            $query->orderBy('student_id', $sortDirection);
        } else {
            $query->orderBy('last_name', $sortDirection)->orderBy('first_name', $sortDirection);
        }

        $students = $query->with(['clearanceShs', 'clearanceCollege'])->get();

        return view('student.clearance.classify', [
            'employeeDeptOffice' => $employee->deptOffice,
            'students' => $students,
            'classifiedShs' => $students->filter(fn($student) => $student->clearanceShs !== null),
            'classifiedCollege' => $students->filter(fn($student) => $student->clearanceCollege !== null),
            'isEmployeeView' => true,
        ]);
    }

    public function submitGraduationClassification(Request $request, Student $student)
    {
        abort_unless(Auth::user()->hasAnyRole(['teacher', 'staff', 'academic_head', 'hr', 'admin']), 403);
        $employee = Auth::user()->employee;
        abort_unless($employee && $employee->deptOffice?->name === "Registrar's Office", 403);

        $validated = $request->validate([
            'classification' => 'required|in:senior_high_school,college_graduating,unclassify',
        ]);

        if ($validated['classification'] === 'senior_high_school') {
            ClearanceCollege::where('student_id', $student->id)->delete();
            ClearanceShs::firstOrCreate(['student_id' => $student->id]);
            $type = 'Senior High School';
        } elseif ($validated['classification'] === 'college_graduating') {
            ClearanceShs::where('student_id', $student->id)->delete();
            ClearanceCollege::firstOrCreate(['student_id' => $student->id]);
            $type = 'College';
        } else {
            // Unclassify - delete both
            ClearanceShs::where('student_id', $student->id)->delete();
            ClearanceCollege::where('student_id', $student->id)->delete();
            $type = 'Unclassified';
        }

        return redirect()
            ->route('employee.clearance.classify')
            ->with('status', "{$student->first_name} {$student->last_name} was classified as {$type}.");
    }

    private function getClearanceDepartmentMapping(string $departmentName): array
    {
        $departmentName = trim($departmentName);
        $columnMapping = $this->getDepartmentColumnMapping();
        abort_unless(isset($columnMapping[$departmentName]), 400, 'Invalid clearance department.');

        return $columnMapping[$departmentName];
    }

    private function getClearanceRecords(Student $student, string $departmentName): array
    {
        $mapping = $this->getClearanceDepartmentMapping($departmentName);
        $student->load(['clearanceShs', 'clearanceCollege']);

        $records = [];

        if ($student->clearanceShs && $mapping['shs']) {
            $records[] = $student->clearanceShs;
        }

        if ($student->clearanceCollege && $mapping['college']) {
            $records[] = $student->clearanceCollege;
        }

        return $records;
    }

    private function getCurrentClearanceValue(Student $student, string $departmentName, string $field)
    {
        $mapping = $this->getClearanceDepartmentMapping($departmentName);
        $records = $this->getClearanceRecords($student, $departmentName);

        foreach ($records as $record) {
            if (!empty($record->{$field})) {
                return $record->{$field};
            }
        }

        return null;
    }

    public function review(Student $student)
    {
        abort_unless(Auth::user()->hasAnyRole(['teacher', 'staff', 'academic_head', 'hr', 'admin']), 403);

        $departmentName = trim(Auth::user()->employee?->deptOffice?->name ?? '');
        abort_unless($departmentName && isset($this->getDepartmentColumnMapping()[$departmentName]), 403);

        $currentStatus = $this->getCurrentClearanceValue($student, $departmentName, $this->getClearanceDepartmentMapping($departmentName)['column']);
        $currentNotes = $this->getCurrentClearanceValue($student, $departmentName, $this->getClearanceDepartmentMapping($departmentName)['remarks']);

        if ($currentStatus === 'approved') {
            $currentStatus = 'sign';
        } elseif ($currentStatus === 'rejected') {
            $currentStatus = 'reject';
        } else {
            $currentStatus = 'sign';
        }

        return view('student.clearance.review', [
            'student' => $student,
            'employeeDeptOffice' => Auth::user()->employee?->deptOffice,
            'isEmployeeView' => true,
            'currentStatus' => $currentStatus,
            'currentNotes' => $currentNotes,
        ]);
    }

    public function submitReview(Request $request, Student $student)
    {
        abort_unless(Auth::user()->hasAnyRole(['teacher', 'staff', 'academic_head', 'hr', 'admin']), 403);

        $validated = $request->validate([
            'status' => 'required|in:sign,reject',
            'notes' => 'nullable|string|max:2000',
        ]);

        $departmentName = trim(Auth::user()->employee?->deptOffice?->name ?? '');
        abort_unless($departmentName && isset($this->getDepartmentColumnMapping()[$departmentName]), 403);

        $mapping = $this->getClearanceDepartmentMapping($departmentName);
        $records = $this->getClearanceRecords($student, $departmentName);
        abort_unless(count($records) > 0, 404, 'No clearance record found for this student.');

        $statusValue = $validated['status'] === 'sign' ? 'approved' : 'rejected';
        $updateData = [
            $mapping['column'] => $statusValue,
            $mapping['remarks'] => $validated['notes'],
        ];

        if ($statusValue === 'approved') {
            $updateData[$mapping['approved_at']] = now();
            $updateData[$mapping['approved_by']] = Auth::user()->employee->id;
        }

        foreach ($records as $record) {
            $updated = $record->update($updateData);
            \Log::info("Clearance update for student {$student->id}, record {$record->id}, updated: {$updated}", $updateData);
        }

        $message = $statusValue === 'approved'
            ? 'Clearance decision approved and saved successfully.'
            : 'Clearance decision rejected and saved successfully.';

        return redirect()
            ->route('employee.clearance.review', $student)
            ->with('status', $message);
    }
}
