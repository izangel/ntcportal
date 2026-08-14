<?php

namespace App\Livewire\Faculty;

use App\Models\CourseBlock;
use App\Models\CourseSyllabus;
use App\Models\Program;
use App\Models\SyllabusLearningPlanItem;
use App\Services\CourseSyllabusData;
use App\Services\ObeSyllabusRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class CourseSyllabusEditor extends Component
{
    public $courseBlockId = null;

    public $programId = null;

    public $grading_system = '';

    public $textbooks_references = '';

    public $course_requirements = '';

    public $classroom_policies = '';

    public $grading_components = [];

    public $items = [];

    public $submittedAt = null;

    public $revisionRequestedAt = null;

    public $revisionRequestedBy = null;

    public $revisionRemarks = null;

    public $confirmSubmit = false;

    public $confirmFinal = false;

    public function mount($courseBlock, $program = null): void
    {
        $block = $this->loadBlock($courseBlock);
        if (! $block) {
            abort(404);
        }

        $this->courseBlockId = $block->id;

        $programId = $this->resolveProgramId($block, $program);
        if (! $programId) {
            abort(404);
        }

        $this->programId = $programId;

        $syllabus = CourseSyllabus::with(['learningPlanItems', 'gradingComponents'])
            ->where('course_block_id', $block->id)
            ->where('program_id', $programId)
            ->first();

        if ($syllabus) {
            $this->grading_system = (string) $syllabus->grading_system;
            $this->textbooks_references = (string) $syllabus->textbooks_references;
            $this->course_requirements = (string) $syllabus->course_requirements;
            $this->classroom_policies = (string) $syllabus->classroom_policies;
$this->submittedAt = $syllabus->submitted_at;
            $this->revisionRequestedAt = $syllabus->revision_requested_at;
            $this->revisionRequestedBy = $syllabus->revision_requested_by_name;
            $this->revisionRemarks = $syllabus->revision_remarks;

            $this->grading_components = $syllabus->gradingComponents
                ->sortBy('sort_order')
                ->map(fn ($component) => [
                    'row_id' => (string) Str::uuid(),
                    'assessment_type' => (string) $component->assessment_type,
                    'percentage' => (float) $component->percentage,
                ])
                ->values()
                ->toArray();

            $this->items = $this->learningPlanItems($syllabus);
        } else {
            $this->items = $this->blankLearningPlan();
        }

        if (empty($this->grading_components)) {
            $this->grading_components = [$this->blankGradingComponent()];
        }
    }

    /**
     * Resolve which program syllabus we are editing: an explicit program
     * segment, else the single program served by the block, else the
     * highest-priority program for mixed blocks.
     */
    private function resolveProgramId(CourseBlock $block, $program): ?int
    {
        $programs = (new CourseSyllabusData($block))->programs();

        if ($program) {
            $programId = (int) $program;

            return $programs->contains('id', $programId) ? $programId : null;
        }

        if ($programs->count() === 1) {
            return (int) $programs->first()->id;
        }

        $default = (new CourseSyllabusData($block))->program();

        return $default?->id;
    }

    /**
     * The learning plan is a fixed 18-week grid: one row per week, from week 1
     * through week 18. The schedule is derived from the row index.
     */
    public const TOTAL_WEEKS = 18;

    /**
     * Weeks designated for examinations. These rows carry no learning plan
     * content — just the examination title centred across the row.
     */
    public const EXAM_WEEKS = [
        5 => 'FIRST EXAMINATION',
        9 => 'SECOND EXAMINATION',
        14 => 'THIRD EXAMINATION',
        18 => 'FINAL EXAMINATION',
    ];

    private function blankLearningPlan(): array
    {
        $rows = [];

        for ($week = 1; $week <= self::TOTAL_WEEKS; $week++) {
            $rows[] = [
                'learning_outcomes' => '',
                'topics_readings' => '',
                'schedule' => 'Week '.$week,
                'learning_activities' => '',
                'assessment_tools' => '',
            ];
        }

        return $rows;
    }

    /**
     * Map an existing syllabus learning plan onto the fixed 18-week grid,
     * filling in any stored content by its sort order (week).
     */
    private function learningPlanItems(CourseSyllabus $syllabus): array
    {
        $stored = $syllabus->learningPlanItems
            ->sortBy('sort_order')
            ->mapWithKeys(fn ($item) => [$item->sort_order => $item])
            ->all();

        $rows = [];

        for ($week = 1; $week <= self::TOTAL_WEEKS; $week++) {
            $item = $stored[$week - 1] ?? null;

            $rows[] = [
                'learning_outcomes' => $item ? (string) $item->learning_outcomes : '',
                'topics_readings' => $item ? (string) $item->topics_readings : '',
                'schedule' => 'Week '.$week,
                'learning_activities' => $item ? (string) $item->learning_activities : '',
                'assessment_tools' => $item ? (string) $item->assessment_tools : '',
            ];
        }

        return $rows;
    }

    private function blankGradingComponent(): array
    {
        return [
            'row_id' => (string) Str::uuid(),
            'assessment_type' => '',
            'percentage' => 0,
        ];
    }

    public function addGradingComponent(): void
    {
        if ($this->isSubmitted()) {
            return;
        }
        $this->grading_components[] = $this->blankGradingComponent();
    }

    /**
     * Load a preset sample grading system.
     */
    public function loadGradingPreset($preset): void
    {
        if ($this->isSubmitted()) {
            return;
        }
        $presets = [
            'lecture' => [
                ['assessment_type' => 'First Exam', 'percentage' => 15],
                ['assessment_type' => 'Second Exam', 'percentage' => 15],
                ['assessment_type' => 'Third Exam', 'percentage' => 15],
                ['assessment_type' => 'Fourth Exam', 'percentage' => 15],
                ['assessment_type' => 'Quizzes & Recitation', 'percentage' => 10],
                ['assessment_type' => 'Assignments & Seatwork', 'percentage' => 5],
                ['assessment_type' => 'Final Project', 'percentage' => 25],
            ],
            'lecture_alt' => [
                ['assessment_type' => 'First Exam', 'percentage' => 15],
                ['assessment_type' => 'Second Exam', 'percentage' => 15],
                ['assessment_type' => 'Third Exam', 'percentage' => 15],
                ['assessment_type' => 'Fourth Exam', 'percentage' => 15],
                ['assessment_type' => 'Quizzes & Recitation', 'percentage' => 10],
                ['assessment_type' => 'Attendance & Participation', 'percentage' => 5],
                ['assessment_type' => 'Final Project', 'percentage' => 25],
            ],
            'lab_flat' => [
                ['assessment_type' => 'First Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Second Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Third Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Fourth Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Laboratory Exercises', 'percentage' => 15],
                ['assessment_type' => 'Laboratory Exams / Skills Test', 'percentage' => 5],
                ['assessment_type' => 'Laboratory Reports & Outputs', 'percentage' => 5],
                ['assessment_type' => 'Final Project', 'percentage' => 15],
            ],
            'lab_split' => [
                ['assessment_type' => 'First Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Second Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Third Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Fourth Exam (Lecture)', 'percentage' => 15],
                ['assessment_type' => 'Quizzes & Recitation (Lecture)', 'percentage' => 5],
                ['assessment_type' => 'Laboratory Performance & Exercises', 'percentage' => 15],
                ['assessment_type' => 'Lab Reports & Final Output', 'percentage' => 5],
                ['assessment_type' => 'Final Project', 'percentage' => 15],
            ],
        ];

        if (! isset($presets[$preset])) {
            return;
        }

        $this->grading_components = array_map(function ($row) {
            return array_merge($row, ['row_id' => (string) Str::uuid()]);
        }, $presets[$preset]);
    }

    /**
     * Load a sample classroom policies and guidelines text.
     */
    public function loadClassroomPoliciesPreset(): void
    {
        if ($this->isSubmitted()) {
            return;
        }
        $this->classroom_policies = "1. Attendance. Attendance is recorded every meeting. A student who misses more than 20% of total class hours for the term (lecture and/or laboratory) may be given a grade of FA (Failure due to Absences) in accordance with school policy. Tardiness of more than 15 minutes is recorded as late; three (3) consecutive lates count as one (1) absence.
2. Punctuality. Be in class and seated before the start of the period. The instructor reserves the right to admit late students only after the first activity has begun, with a mark of \"late.\"
3. Preparedness. Bring required materials (module, laboratory manual, ID, and personal protective equipment where applicable) to every session. Come to class having reviewed the assigned reading or pre-laboratory exercise.
4. Academic Integrity. All quizzes, examinations, laboratory exercises, and outputs must be your own work. Plagiarism, cheating, unauthorized collaboration, and submission of another person's work as your own will result in a grade of zero for the activity and may be subject to disciplinary action.
5. Submissions. Requirements are due on the scheduled date and time. Late submissions are accepted only within a grace period set by the instructor (e.g., until the next meeting) and will incur a deduction (e.g., 20% of the score). No submissions are accepted after the graded activity has been returned or discussed.
6. Mobile Devices & Electronic Gadgets. Mobile phones and gadgets must be kept silent and out of sight during discussions and examinations, unless an activity requires their use. Unauthorized use during an exam is treated as a violation of academic integrity.
7. Classroom Decorum. Show respect toward the instructor and classmates at all times. Refrain from disruptive behavior, side conversations, and unnecessary noise during discussions and laboratory work.
8. Make-up Examinations. Make-up exams are granted only for valid, documented reasons (e.g., illness with a medical certificate, approved school activity, or family emergency) and must be requested within one week after the missed examination.
9. Safety in the Laboratory. (For courses with laboratory) Follow all safety rules at all times. Report accidents, breakage, or malfunctioning equipment to the instructor immediately. Unauthorized experiments or misuse of equipment may result in exclusion from the activity and a zero grade.
10. Consultation. For questions or concerns about lessons, grades, or requirements, consult with the instructor during consultation hours or by appointment.";
    }

    public function removeGradingComponent($rowId): void
    {
        if ($this->isSubmitted()) {
            return;
        }
        $this->grading_components = collect($this->grading_components)
            ->reject(fn ($component) => ($component['row_id'] ?? null) == $rowId)
            ->values()
            ->all();

        if (empty($this->grading_components)) {
            $this->grading_components[] = $this->blankGradingComponent();
        }
    }

    public function gradingTotal(): float
    {
        return (float) array_sum(array_column($this->grading_components, 'percentage'));
    }

    private function loadBlock($courseBlock): ?CourseBlock
    {
        return CourseBlock::with(['course', 'academicYear'])
            ->whereKey($courseBlock)
            ->where('faculty_id', Auth::user()?->employee?->id)
            ->first();
    }

    /**
     * The learning plan is a fixed 18-week grid, so rows cannot be added or
     * removed by the teaching faculty.
     */

    /**
     * Whether this syllabus has already been submitted and is therefore final
     * and locked. Submitted syllabi can no longer be edited.
     */
    private function isSubmitted(): bool
    {
        return ! is_null($this->submittedAt);
    }

    /**
     * Save the syllabus as a draft. Drafts are allowed to be incomplete, so no
     * validation is applied; the teaching faculty can save progress at any time.
     * Once submitted the syllabus is locked and can no longer be saved.
     */
    public function save(): void
    {
        if ($this->isSubmitted()) {
            return;
        }

        $this->persist();

        $this->toast('success', 'Syllabus draft saved successfully.');
    }

    private function toast(string $type, string $message): void
    {
        $this->dispatch('toast', type: $type, message: $message);
    }

    /**
     * Open the completion steps before submission. If the syllabus is
     * incomplete, the confirmation step is NOT shown; the specific missing
     * items are reported instead so the faculty can fix them first. Only a
     * complete syllabus reaches the final confirmation.
     */
    public function openSubmitConfirmation(): void
    {
        if ($this->isSubmitted()) {
            return;
        }

        if ($this->addCompletenessErrors()) {
            $this->toast('error', 'Syllabus is incomplete and cannot be submitted. Fix the missing items above, save as a draft, and try again when everything is complete.');

            return;
        }

        $this->resetErrorBag();
        $this->confirmFinal = false;
        $this->confirmSubmit = true;
    }

    public function cancelSubmitConfirmation(): void
    {
        $this->confirmSubmit = false;
        $this->confirmFinal = false;
        $this->resetErrorBag();
    }

    /**
     * Add validation errors for any missing/incomplete parts of the syllabus.
     *
     * @return bool true when the syllabus is incomplete, false when complete
     */
    private function addCompletenessErrors(): bool
    {
        $block = $this->loadBlock($this->courseBlockId);
        if (! $block) {
            abort(404);
        }

        $program = Program::find($this->programId);

        $gradingViolations = $this->gradingViolations();
        foreach ($gradingViolations as $violation) {
            $this->addError('grading_components', $violation);
        }

        $ruleViolations = ObeSyllabusRules::violations($block, $program);
        foreach ($ruleViolations as $violation) {
            $this->addError('syllabus_rules', $violation);
        }

        $planViolations = $this->learningPlanViolations();
        foreach ($planViolations as $violation) {
            $this->addError('learning_plan', $violation);
        }

        if (! empty($gradingViolations) || ! empty($ruleViolations) || ! empty($planViolations)) {
            $this->addError('submission', 'Syllabus is incomplete and cannot be submitted. Fix the missing items above, save as a draft, and try again when everything is complete.');

            return true;
        }

        return false;
    }

    /**
     * Validate the learning plan: every week (row 1 through 18) must have its
     * learning outcomes, topics & readings, learning activities, and assessment
     * tools completed before the syllabus can be submitted. Examination weeks
     * (5, 9, 14, 18) are exempt — they carry no learning-plan content.
     *
     * @return array<int, string>
     */
    private function learningPlanViolations(): array
    {
        $violations = [];

        foreach (array_values($this->items) as $index => $item) {
            if (isset(self::EXAM_WEEKS[$index + 1])) {
                continue;
            }

            $missing = [];

            foreach ([
                'learning_outcomes' => 'Learning Outcomes',
                'topics_readings' => 'Topics & Readings',
                'learning_activities' => 'Learning Activities',
                'assessment_tools' => 'Assessment Tools',
            ] as $field => $label) {
                if (trim((string) ($item[$field] ?? '')) === '') {
                    $missing[] = $label;
                }
            }

            if (! empty($missing)) {
                $violations[] = 'Week '.($index + 1).' is incomplete ('.$this->requirementsLabel($missing).').';
            }
        }

        return $violations;
    }

    private function requirementsLabel(array $labels): string
    {
        if (count($labels) === 1) {
            return $labels[0];
        }

        $last = array_pop($labels);

        return implode(', ', $labels).' and '.$last;
    }

    /**
     * Complete the submission. Re-checks that the syllabus is complete; if it
     * is, it is persisted, marked as submitted, and locked. Once submitted the
     * contents (schedule, learning plan, assessment tasks) are final.
     */
    public function submit(): void
    {
        if ($this->isSubmitted()) {
            return;
        }

        if ($this->addCompletenessErrors()) {
            $this->confirmSubmit = false;
            $this->confirmFinal = false;
            $this->toast('error', 'Syllabus is incomplete and cannot be submitted. Fix the missing items above, save as a draft, and try again when everything is complete.');

            return;
        }

        if (! $this->confirmFinal) {
            $this->addError('confirm_final', 'Please confirm that the syllabus is final before submitting.');

            return;
        }

        $this->persist(submitted: true);
        $this->confirmSubmit = false;
        $this->confirmFinal = false;

        $this->toast('success', 'Syllabus submitted and locked. It can no longer be edited.');
    }

    private function persist(bool $submitted = false): void
    {
        $block = $this->loadBlock($this->courseBlockId);
        if (! $block) {
            abort(404);
        }

        $syllabus = CourseSyllabus::firstOrCreate(
            [
                'course_block_id' => $block->id,
                'program_id' => $this->programId,
            ],
            [
                'course_block_id' => $block->id,
                'program_id' => $this->programId,
            ]
        );

        $syllabus->update([
            'grading_system' => $this->grading_system,
            'textbooks_references' => $this->textbooks_references,
            'course_requirements' => $this->course_requirements,
            'classroom_policies' => $this->classroom_policies,
            'submitted_at' => $submitted ? now() : $syllabus->submitted_at,
        ]);

        $this->submittedAt = $syllabus->submitted_at;

        $syllabus->gradingComponents()->delete();
        foreach (array_values($this->grading_components) as $i => $component) {
            $syllabus->gradingComponents()->create([
                'assessment_type' => $component['assessment_type'] ?? null,
                'percentage' => (float) ($component['percentage'] ?? 0),
                'sort_order' => $i,
            ]);
        }

        SyllabusLearningPlanItem::where('course_syllabus_id', $syllabus->id)->delete();

        foreach (array_values($this->items) as $i => $item) {
            SyllabusLearningPlanItem::create([
                'course_syllabus_id' => $syllabus->id,
                'learning_outcomes' => $item['learning_outcomes'] ?? null,
                'topics_readings' => $item['topics_readings'] ?? null,
                'schedule' => 'Week '.($i + 1),
                'learning_activities' => $item['learning_activities'] ?? null,
                'assessment_tools' => $item['assessment_tools'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * Validate the structured grading components: each must have an assessment
     * type, a positive percentage, and all percentages must total exactly 100%.
     *
     * @return array<int, string>
     */
    private function gradingViolations(): array
    {
        $components = array_values($this->grading_components);

        $violations = [];

        foreach ($components as $index => $component) {
            $type = trim((string) ($component['assessment_type'] ?? ''));
            $percentage = (float) ($component['percentage'] ?? 0);

            if ($type === '') {
                $violations[] = 'Row '.($index + 1).': please provide an assessment/requirement type.';
            }

            if ($percentage <= 0) {
                $violations[] = 'Row '.($index + 1).': percentage must be greater than 0.';
            }
        }

        $total = (float) array_sum(array_column($components, 'percentage'));

        if (abs($total - 100.0) > 0.001) {
            $violations[] = "Grading percentages must total 100%; current total is {$total}%.";
        }

        return $violations;
    }

    public function data(): ?CourseSyllabusData
    {
        $block = $this->loadBlock($this->courseBlockId);

        if (! $block) {
            return null;
        }

        $program = Program::find($this->programId);

        return $program ? new CourseSyllabusData($block, $program) : null;
    }

    public function programs()
    {
        $block = $this->loadBlock($this->courseBlockId);

        return $block ? (new CourseSyllabusData($block))->programs() : collect();
    }

    /**
     * Refresh the CO-PO matrix and required-tasks banner when the embedded
     * assessment-task setup saves, deletes, or re-maps an item.
     */
    #[On('assessment-tasks-updated')]
    public function refreshAssessmentTasks(): void {}

    public function render()
    {
        $data = $this->data();

        $violations = collect();
        if ($data && $data->program()) {
            $violations = collect(ObeSyllabusRules::violations(
                $data->block(),
                $data->program()
            ));
        }

        return view('livewire.faculty.course-syllabus-editor', [
            'data' => $data,
            'items' => $this->items,
            'programs' => $this->programs(),
            'tasks' => $data ? $data->assessmentTasks() : collect(),
            'courseBlockId' => $this->courseBlockId,
            'programId' => $this->programId,
            'ruleViolations' => $violations,
            'examWeeks' => self::EXAM_WEEKS,
        ])->extends('layouts.admin')->section('content');
    }
}
