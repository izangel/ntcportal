<?php

namespace App\Livewire\Admin;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Masterlist of all students with duplicate detection.
 *
 * A student is flagged as a duplicate when it shares any of these with
 * another student record:
 *   - the same email
 *   - the same student_id (school ID)
 *   - the same full name (first + last [+ middle / birthday])
 *
 * Matching rows are grouped so the registrar can see the whole duplicate set.
 */
class StudentMasterlist extends Component
{
    use WithPagination;

    public $q = '';

    public $showDuplicatesOnly = false;

    public $duplicateType = 'all'; // all | email | student_id | name

    public function updatedQ()
    {
        $this->resetPage();
    }

    public function updatedShowDuplicatesOnly()
    {
        $this->resetPage();
    }

    public function updatedDuplicateType()
    {
        $this->resetPage();
    }

    public function studentName(Student $s): string
    {
        return trim($s->last_name.', '.$s->first_name.($s->middle_name ? ' '.$s->middle_name : ''));
    }

    private function keyEmail(Student $s): string
    {
        return strtolower(trim((string) $s->email));
    }

    private function keyStudentId(Student $s): string
    {
        return strtolower(trim((string) $s->student_id));
    }

    private function keyName(Student $s): string
    {
        return strtolower(trim($s->first_name.'|'.$s->last_name.'|'.($s->birthday?->toDateString() ?? '')));
    }

    /**
     * Check what records in OTHER tables reference this student. Deleting a
     * student cascades (or is blocked by) these, so we refuse the delete when
     * anything exists — the caller must first clear the related records.
     */
    private function dependentRecordSummary(Student $s): array
    {
        $id = $s->id;

        $checks = [
            'Enrollment records' => DB::table('enrollments')->where('student_id', $id)->count(),
            'Section memberships' => DB::table('section_student')->where('student_id', $id)->count(),
            'Course block memberships' => DB::table('student_courseblock')->where('student_id', $id)->count(),
            'Attendance records' => DB::table('attendance_records')->where('student_id', $id)->count(),
            'Course evaluations' => DB::table('course_evaluations')->where('student_id', $id)->count(),
            'Faculty evaluations' => DB::table('faculty_evaluations')->where('student_id', $id)->count(),
            'Candidacy records' => DB::table('candidacies')->where('student_id', $id)->count(),
            'Election votes' => DB::table('election_votes')->where('student_id', $id)->count(),
            'Student-course records' => DB::table('student_course')->where('student_id', $id)->count(),
            'Assessment marks' => DB::table('student_assessment_marks')->where('student_id', $id)->count(),
        ];

        return array_filter($checks, fn ($count) => $count > 0);
    }

    /**
     * Delete a student ONLY when no other table references the record.
     * The linked user account is removed too (it exists solely for login).
     */
    public function deleteStudent(int $studentId): void
    {
        $student = Student::find($studentId);

        if (! $student) {
            session()->flash('error', 'Student record not found.');
            return;
        }

        $deps = $this->dependentRecordSummary($student);

        if (! empty($deps)) {
            $list = collect($deps)->map(fn ($n, $label) => "{$label} ({$n})")->implode(', ');
            session()->flash('error', "Cannot delete {$student->last_name}, {$student->first_name} while related records exist: {$list}. Remove those records first, or mark the student inactive instead.");
            return;
        }

        DB::transaction(function () use ($student) {
            // Remove the login account only when NO other student shares it
            // (students.user_id -> users is ON DELETE CASCADE, so deleting a
            // shared user would delete the other student records too).
            if ($student->user_id) {
                $othersShare = Student::where('user_id', $student->user_id)
                    ->where('id', '!=', $student->id)
                    ->exists();

                if ($othersShare) {
                    $student->update(['user_id' => null]);
                } else {
                    $user = User::find($student->user_id);
                    if ($user) {
                        $user->roles()->detach();
                        $user->delete();
                    }
                }
            }

            $student->delete();
        });

        session()->flash('success', "Deleted student record for {$student->last_name}, {$student->first_name}. Any other records pointing to a shared login account were left untouched.");
    }

    public function editStudent(int $studentId)
    {
        $student = Student::find($studentId);

        if (! $student) {
            session()->flash('error', 'Student record not found.');
            return;
        }

        return redirect()->route('students.edit', ['student' => $student->id]);
    }

    /**
     * Load all students, compute duplicate group membership in-memory, and
     * attach the flags used by the view. The dataset (~<2000 rows) is small
     * enough that this avoids N+1 queries.
     *
     * @return array{students: Collection, totals: array}
     */
    public function buildMasterlist(): array
    {
        $students = Student::with('sections.program')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $emailGroups = $students->filter(fn ($s) => $this->keyEmail($s) !== '')
            ->groupBy(fn ($s) => $this->keyEmail($s))
            ->filter(fn ($g) => $g->count() > 1);

        $idGroups = $students->filter(fn ($s) => $this->keyStudentId($s) !== '')
            ->groupBy(fn ($s) => $this->keyStudentId($s))
            ->filter(fn ($g) => $g->count() > 1);

        $nameGroups = $students->groupBy(fn ($s) => $this->keyName($s))
            ->filter(fn ($g) => $g->count() > 1);

        $sections = $students->each(function ($s) {
            $programs = $s->sections
                ->pluck('program.name')
                ->filter()
                ->unique()
                ->implode(', ');

            $s->setAttribute('programs', $programs);
            $s->setAttribute('section_names', $s->sections->pluck('name')->filter()->unique()->implode(', '));
        });

        $duplicateRecords = [];

        foreach ($students as $s) {
            $flags = [];
            $email = $this->keyEmail($s);
            $id = $this->keyStudentId($s);
            $name = $this->keyName($s);

            $emailGroup = $email !== '' ? ($emailGroups->get($email) ?? null) : null;
            $idGroup = $id !== '' ? ($idGroups->get($id) ?? null) : null;
            $nameGroup = $nameGroups->get($name) ?? null;

            if ($emailGroup !== null) {
                $flags[] = ['type' => 'email', 'matches' => $emailGroup->reject(fn ($x) => $x->id === $s->id)->pluck('id')->values()];
            }
            if ($idGroup !== null) {
                $flags[] = ['type' => 'student_id', 'matches' => $idGroup->reject(fn ($x) => $x->id === $s->id)->pluck('id')->values()];
            }
            if ($nameGroup !== null) {
                $flags[] = ['type' => 'name', 'matches' => $nameGroup->reject(fn ($x) => $x->id === $s->id)->pluck('id')->values()];
            }

            $s->setAttribute('duplicate_flags', $flags);
            $s->setAttribute('is_duplicate', ! empty($flags));
            $s->setAttribute('duplicate_reasons', collect($flags)->pluck('type'));
        }

        // Filtering
        $filtered = $students;

        if ($this->showDuplicatesOnly) {
            $filtered = $filtered->filter(fn ($s) => $s->is_duplicate);
            if ($this->duplicateType !== 'all') {
                $filtered = $filtered->filter(fn ($s) => $s->duplicate_reasons->contains($this->duplicateType));
            }
        }

        if (trim($this->q) !== '') {
            $term = strtolower(trim($this->q));
            $filtered = $filtered->filter(function ($s) use ($term) {
                if (str_contains(strtolower($s->first_name), $term)) {
                    return true;
                }
                if (str_contains(strtolower($s->last_name), $term)) {
                    return true;
                }
                if (str_contains(strtolower((string) $s->student_id), $term)) {
                    return true;
                }
                if (str_contains(strtolower((string) $s->email), $term)) {
                    return true;
                }
                return false;
            });
        }

        $totals = [
            'total' => $students->count(),
            'duplicates' => $students->filter(fn ($s) => $s->is_duplicate)->count(),
            'by_email' => $students->filter(fn ($s) => $s->duplicate_reasons->contains('email'))->count(),
            'by_student_id' => $students->filter(fn ($s) => $s->duplicate_reasons->contains('student_id'))->count(),
            'by_name' => $students->filter(fn ($s) => $s->duplicate_reasons->contains('name'))->count(),
        ];

        return ['students' => $filtered, 'totals' => $totals];
    }

    public function render()
    {
        $data = $this->buildMasterlist();

        return view('livewire.admin.student-masterlist', [
            'students' => $data['students']->values(),
            'totals' => $data['totals'],
        ])->extends('layouts.admin')
            ->section('content');
    }
}