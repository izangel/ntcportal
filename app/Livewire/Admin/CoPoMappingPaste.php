<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\AcademicYear;
use App\Models\BloomsTaxonomy;
use App\Models\ProgramOutcome;
use App\Models\CourseLearningOutcome;
use Illuminate\Support\Facades\DB;

/**
 * CO-PO matrix editor for heads: paste the I/E/D map straight from Excel
 * (tab/newline separated) to bulk-set the CLO → PO levels for one course.
 *
 * The pivot stores I/G/A internally; the UI/Excel convention is I/E/D.
 *   I  -> Introduced   (stored 'I')
 *   E  -> Enabling     (stored 'G')
 *   D  -> Demonstrating (stored 'A')
 */
class CoPoMappingPaste extends Component
{
    public $selectedProgramId = null;
    public $selectedBatchYear = null;
    public $selectedCourseId = null;

    public $sourceProgramId = null;
    public $sourceBatchYear = null;
    public $sourceCourseId = null;

    public $pastedText = '';
    public $parseMessage = null;
    public $parseType = 'info';
    public $applied = 0;

    // Add/edit CLO form state.
    public $editingCloId = null;
    public $cloCode = '';
    public $cloDescription = '';
    public $cloTaxonomyId = null;

    // Bulk CLO paste state.
    public $cloPasteText = '';

    public function updatedSelectedProgramId()
    {
        $this->reset(['selectedCourseId', 'pastedText', 'parseMessage', 'parseType', 'applied']);
        $this->resetCloForm();
    }

    public function updatedSelectedBatchYear()
    {
        $this->reset(['selectedCourseId', 'pastedText', 'parseMessage', 'parseType', 'applied']);
        $this->resetCloForm();
    }

    public function updatedSelectedCourseId()
    {
        $this->reset(['pastedText', 'parseMessage', 'parseType', 'applied']);
    }

    public function updatedSourceProgramId()
    {
        $this->reset(['sourceCourseId']);
    }

    public function updatedSourceBatchYear()
    {
        $this->reset(['sourceCourseId']);
    }

    /**
     * Build a tab-separated template (current mapping) so the head can paste
     * it into Excel, tweak it, and paste it back here.
     */
    public function generateTemplate(): void
    {
        $course = Course::find($this->selectedCourseId);
        $clos = $this->courseClos($course);
        $programOutcomes = $this->programOutcomes();

        if ($clos->isEmpty() || $programOutcomes->isEmpty()) {
            $this->parseType = 'error';
            $this->parseMessage = 'No CLOs or POs available for this course and batch — nothing to template.';
            return;
        }

        $header = collect(['CLO'])->merge($programOutcomes->pluck('code'));
        $lines = [$header->implode("\t")];

        foreach ($clos as $clo) {
            $row = collect([$clo->code]);
            foreach ($programOutcomes as $po) {
                $level = (string) $clo->programOutcomes->firstWhere('id', $po->id)?->pivot?->level ?? '';
                $row->push($this->toDisplayLevel($level));
            }
            $lines[] = $row->implode("\t");
        }

        $this->pastedText = implode("\n", $lines);
        $this->parseType = 'info';
        $this->parseMessage = 'Template generated. Copy it into Excel, edit the I/E/D cells, then paste it back here and click "Apply Mapping".';
    }

    public function saveClo(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedCourseId' => 'required|exists:courses,id',
            'cloCode' => 'required|string|max:20',
            'cloDescription' => 'required|string|min:10',
            'cloTaxonomyId' => 'required|exists:blooms_taxonomies,id',
        ]);

        $course = Course::findOrFail($this->selectedCourseId);

        $isAssignedToProgram = Program::findOrFail($this->selectedProgramId)
            ->courses()
            ->when($this->selectedBatchYear, function ($query) {
                $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
            })
            ->where('courses.id', $course->id)
            ->exists();

        if (! $isAssignedToProgram) {
            $this->addError('selectedCourseId', 'This course is not assigned to the selected program and batch.');
            return;
        }

        $data = [
            'course_id' => $course->id,
            'code' => $this->cloCode,
            'description' => $this->cloDescription,
            'blooms_taxonomy_id' => $this->cloTaxonomyId,
            'effective_batch_year' => $this->selectedBatchYear ?: null,
        ];

        if ($this->editingCloId) {
            CourseLearningOutcome::findOrFail($this->editingCloId)->update($data);
            $message = 'CLO updated successfully.';
        } else {
            CourseLearningOutcome::create($data);
            $message = "CLO {$this->cloCode} added to {$course->code}.";
        }

        $this->resetCloForm();
        $this->parseType = 'success';
        $this->parseMessage = $message;
    }

    public function editClo(int $cloId): void
    {
        $clo = CourseLearningOutcome::query()
            ->whereKey($cloId)
            ->where('effective_batch_year', $this->selectedBatchYear ?: null)
            ->firstOrFail();

        $this->editingCloId = $clo->id;
        $this->cloCode = (string) $clo->code;
        $this->cloDescription = (string) $clo->description;
        $this->cloTaxonomyId = $clo->blooms_taxonomy_id;
        $this->resetErrorBag();
    }

    public function deleteClo(int $cloId): void
    {
        $clo = CourseLearningOutcome::query()
            ->whereKey($cloId)
            ->where('effective_batch_year', $this->selectedBatchYear ?: null)
            ->first();

        if (! $clo) {
            return;
        }

        DB::transaction(function () use ($clo) {
            $clo->programOutcomes()->detach();
            $clo->delete();
        });

        $this->parseType = 'success';
        $this->parseMessage = "CLO {$clo->code} deleted.";

        if ((int) $this->editingCloId === $clo->id) {
            $this->resetCloForm();
        }
    }

    private function resetCloForm(): void
    {
        $this->reset(['editingCloId', 'cloCode', 'cloDescription', 'cloTaxonomyId']);
        $this->resetErrorBag();
    }

    /**
     * Bulk-add CLOs from a pasted grid.
     *
     * Expected format (tab or comma separated, one CLO per line):
     *   CLO Code | Description | Bloom's Taxonomy
     * The taxonomy cell accepts a code (e.g. C1), "C2 - Understanding", or a
     * keyword (e.g. "Analyzing"); it may also be left blank.
     */
    public function addClosFromPaste(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedCourseId' => 'required|exists:courses,id',
            'cloPasteText' => 'required|string',
        ]);

        $course = Course::findOrFail($this->selectedCourseId);

        $isAssignedToProgram = Program::findOrFail($this->selectedProgramId)
            ->courses()
            ->when($this->selectedBatchYear, function ($query) {
                $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
            })
            ->where('courses.id', $course->id)
            ->exists();

        if (! $isAssignedToProgram) {
            $this->addError('selectedCourseId', 'This course is not assigned to the selected program and batch.');
            return;
        }

        $taxonomies = BloomsTaxonomy::all();

        $lines = preg_split('/\r\n|\r|\n/', trim($this->cloPasteText));
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        if (empty($lines)) {
            $this->parseType = 'error';
            $this->parseMessage = 'No CLO rows detected in the pasted content.';
            return;
        }

        $existing = $this->courseClos($course)
            ->pluck('code')
            ->map(fn ($c) => strtoupper(trim((string) $c)))
            ->flip();

        // Skip a header row (e.g. "Code, Description, Bloom's Taxonomy").
        $firstCells = str_contains($lines[0], "\t")
            ? preg_split('/\t+/', $lines[0])
            : preg_split('/,/', $lines[0]);
        $firstLower = strtolower(trim((string) ($firstCells[0] ?? '')));
        if (in_array($firstLower, ['code', 'clo code', 'clo_code', 'clocode'], true)) {
            array_shift($lines);
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = [];

        DB::transaction(function () use ($lines, $course, $taxonomies, $existing, &$created, &$updated, &$skipped, &$errors) {
            foreach ($lines as $line) {
                // Expect Code | Description | Bloom's Taxonomy. Prefer tabs when
                // present (descriptions often contain commas); fall back to a
                // comma split with the LAST cell treated as taxonomy.
                if (str_contains($line, "\t")) {
                    $cells = preg_split('/\t+/', $line);
                    $cells = array_values(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''));
                    $code = strtoupper(trim((string) ($cells[0] ?? '')));
                    $description = trim((string) ($cells[1] ?? ''));
                    $taxonomyRaw = trim((string) ($cells[2] ?? ''));
                } else {
                    $parts = preg_split('/,/', $line);
                    $parts = array_values(array_filter(array_map('trim', $parts), fn ($c) => $c !== ''));
                    $code = strtoupper(trim((string) ($parts[0] ?? '')));
                    // Everything between the code and the last cell is part of the
                    // description; the last cell is the taxonomy (only when it
                    // looks like a taxonomy, otherwise it stays part of the text).
                    $descriptionParts = array_slice($parts, 1);
                    $last = count($descriptionParts) > 0 ? array_pop($descriptionParts) : '';
                    $taxonomyRaw = $this->matchTaxonomy($last, $taxonomies) ? $last : '';
                    if ($taxonomyRaw === '') {
                        $descriptionParts[] = $last;
                    }
                    $description = implode(', ', $descriptionParts);
                }

                if ($code === '' || $description === '') {
                    $errors[] = "Skipped row '{$line}' — code and description are both required.";
                    continue;
                }

                $taxonomy = $taxonomyRaw !== '' ? $this->matchTaxonomy($taxonomyRaw, $taxonomies) : null;

                if ($taxonomyRaw !== '' && ! $taxonomy) {
                    $errors[] = "Row {$code}: unknown Bloom's taxonomy '{$taxonomyRaw}' (use a code like C1, or a level name).";
                    continue;
                }

                // blooms_taxonomy_id is NOT NULL in the schema, so an empty taxonomy
                // falls back to the first level (C1 - Remembering) when available.
                $taxonomy = $taxonomy ?? $taxonomies->first();

                if (! $taxonomy) {
                    $errors[] = "Row {$code}: no Bloom's taxonomy configured in the system to assign.";
                    continue;
                }

                $data = [
                    'course_id' => $course->id,
                    'code' => $code,
                    'description' => $description,
                    'blooms_taxonomy_id' => $taxonomy->id,
                    'effective_batch_year' => $this->selectedBatchYear ?: null,
                ];

                if ($existing->has($code)) {
                    $clo = CourseLearningOutcome::where('course_id', $course->id)
                        ->where('effective_batch_year', $this->selectedBatchYear ?: null)
                        ->whereRaw('UPPER(TRIM(code)) = ?', [$code])
                        ->firstOrFail();
                    $clo->update($data);
                    $updated++;
                } else {
                    CourseLearningOutcome::create($data);
                    $created++;
                }
            }
        });

        $messages = [];
        if ($created > 0) {
            $messages[] = "Added {$created} new CLO(s) to {$course->code}.";
        }
        if ($updated > 0) {
            $messages[] = "Updated {$updated} existing CLO(s).";
        }
        if ($created === 0 && $updated === 0) {
            $messages[] = 'No CLOs were added or updated.';
        }
        if (! empty($errors)) {
            $messages[] = implode(' ', array_slice($errors, 0, 4)) . (count($errors) > 4 ? " (+".(count($errors) - 4)." more)" : '');
        }

        $this->parseType = ($created > 0 || $updated > 0) ? 'success' : 'info';
        $this->parseMessage = implode(' ', $messages);
        $this->cloPasteText = '';
    }

    private function matchTaxonomy(string $raw, $taxonomies): ?BloomsTaxonomy
    {
        $v = strtolower(trim($raw));
        $code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $raw));

        // Exact code match first (e.g. "C1", "A2", "P1").
        $byCode = $taxonomies->first(fn ($t) => strtoupper(trim($t->code)) === $code);
        if ($byCode) {
            return $byCode;
        }

        // Fuzzy keyword match against code, level, or domain.
        return $taxonomies->first(function ($t) use ($v, $code) {
            $haystack = strtolower($t->code . ' ' . $t->level . ' ' . $t->domain);
            return str_contains($haystack, $v) || $code === $t->level;
        });
    }

    public function applyMapping(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedCourseId' => 'required|exists:courses,id',
            'pastedText' => 'required|string',
        ]);

        $course = Course::find($this->selectedCourseId);
        $programOutcomes = $this->programOutcomes();
        $clos = $this->courseClos($course);

        if ($programOutcomes->isEmpty()) {
            $this->parseType = 'error';
            $this->parseMessage = 'No Program Outcomes found for this program and batch. Add POs first.';
            return;
        }

        if ($clos->isEmpty()) {
            $this->parseType = 'error';
            $this->parseMessage = "{$course->code} has no CLOs to map.";
            return;
        }

        [$rows, $headerCodes] = $this->parsePastedText($this->pastedText);

        if ($rows->isEmpty()) {
            $this->parseType = 'error';
            $this->parseMessage = 'No rows detected in the pasted content. Make sure each row is on its own line (tab-separated from Excel).';
            return;
        }

        $posByCode = $programOutcomes->keyBy(fn ($po) => strtoupper(trim((string) $po->code)));
        $closByCode = $clos->keyBy(fn ($clo) => strtoupper(trim((string) $clo->code)));

        $unknownPos = collect($headerCodes)->diff($posByCode->keys());
        $unknownClos = $rows->pluck('clo_code')->diff($closByCode->keys());

        $warnings = collect();
        if ($unknownPos->isNotEmpty()) {
            $warnings->push('Unknown PO codes (skipped): ' . $unknownPos->join(', '));
        }
        if ($unknownClos->isNotEmpty()) {
            $warnings->push('Unknown CLO codes (skipped): ' . $unknownClos->join(', '));
        }

        $applied = 0;
        $cleared = 0;

        DB::transaction(function () use ($rows, $headerCodes, $posByCode, $closByCode, &$applied, &$cleared) {
            foreach ($rows as $row) {
                $clo = $closByCode->get($row['clo_code']);
                if (! $clo) {
                    continue;
                }

                $sync = [];
                foreach ($headerCodes as $i => $code) {
                    $po = $posByCode->get($code);
                    if (! $po) {
                        continue;
                    }

                    $raw = $row['levels'][$i] ?? '';
                    $level = $this->toStoredLevel($raw);

                    if ($level === null) {
                        $cleared++;
                        $clo->programOutcomes()->detach($po->id);
                    } else {
                        $sync[$po->id] = ['level' => $level];
                    }
                }

                if ($sync) {
                    $clo->programOutcomes()->syncWithoutDetaching($sync);
                    $applied += count($sync);
                }
            }
        });

        $this->applied = $applied;

        $messages = [];
        if ($applied > 0) {
            $messages[] = "Applied {$applied} CO-PO mapping(s).";
        } elseif ($cleared === 0) {
            $messages[] = 'Nothing to change — the mapping already matches the pasted values.';
        }
        if ($cleared > 0) {
            $messages[] = "Cleared {$cleared} mapping(s) where the cell was blank.";
        }
        $messages = array_merge($messages, $warnings->all());

        $this->parseType = ($applied > 0 || $cleared > 0) ? 'success' : 'info';
        $this->parseMessage = implode(' ', $messages);
    }

    /**
     * Copy the CO-PO mapping for the selected course from a source program +
     * batch into the current program + batch. CLOs are matched by code and POs
     * by code, so the mapping is only copied to target POs with a matching code.
     */
    public function copyFromProgramBatch(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedBatchYear' => 'required',
            'selectedCourseId' => 'required|exists:courses,id',
            'sourceProgramId' => 'required|exists:programs,id',
            'sourceBatchYear' => 'required',
        ]);

        $targetBatch = (string) $this->selectedBatchYear;
        $sourceBatch = (string) $this->sourceBatchYear;

        if (
            (int) $this->sourceProgramId === (int) $this->selectedProgramId
            && $sourceBatch === $targetBatch
        ) {
            $this->parseType = 'error';
            $this->parseMessage = 'The source program and batch must differ from the target.';
            return;
        }

        $targetCourse = Course::find($this->selectedCourseId);

        // Allow copying from a differently-named source course (e.g. an
        // equivalent course in another program); default to the target course.
        $sourceCourse = $this->sourceCourseId
            ? Course::find($this->sourceCourseId)
            : $targetCourse;

        if (! $sourceCourse) {
            $this->parseType = 'error';
            $this->parseMessage = 'Select a source course to copy from.';
            return;
        }

        // Target-side CLOs (of the target course for the target batch).
        $targetClos = $this->courseClos($targetCourse)
            ->keyBy(fn ($clo) => strtoupper(trim((string) $clo->code)));

        // Source-side CLOs (of the source course for the source program + batch).
        $sourceClos = CourseLearningOutcome::with('programOutcomes')
            ->where('course_id', $sourceCourse->id)
            ->where('effective_batch_year', $sourceBatch)
            ->orderBy('code')
            ->get()
            ->keyBy(fn ($clo) => strtoupper(trim((string) $clo->code)));

        if ($sourceClos->isEmpty()) {
            $this->parseType = 'error';
            $this->parseMessage = "No CLOs found for {$sourceCourse->code} in the source program/batch (Batch {$sourceBatch}).";
            return;
        }

        if ($targetClos->isEmpty()) {
            // The target may have no CLOs yet — that's fine; they'll be created
            // from the source below.
            $targetClos = collect();
        }

        // Target POs keyed by code so we only link matching outcomes.
        $targetPosByCode = ProgramOutcome::query()
            ->where('program_id', $this->selectedProgramId)
            ->where('effective_batch_year', $targetBatch)
            ->get()
            ->keyBy(fn ($po) => strtoupper(trim((string) $po->code)));

        if ($targetPosByCode->isEmpty()) {
            $this->parseType = 'error';
            $this->parseMessage = 'The target program/batch has no Program Outcomes to map to.';
            return;
        }

        $mapped = 0;
        $createdClos = 0;
        $skippedPo = 0;
        $skippedClo = 0;

        DB::transaction(function () use ($sourceClos, $targetClos, $targetPosByCode, $targetCourse, $targetBatch, &$mapped, &$createdClos, &$skippedPo, &$skippedClo) {
            foreach ($sourceClos as $code => $sourceClo) {
                $targetClo = $targetClos->get($code);

                if (! $targetClo) {
                    // The CLO doesn't exist in the target yet — copy it in.
                    $targetClo = CourseLearningOutcome::create([
                        'course_id' => $targetCourse->id,
                        'code' => $sourceClo->code,
                        'description' => $sourceClo->description,
                        'blooms_taxonomy_id' => $sourceClo->blooms_taxonomy_id,
                        'effective_batch_year' => $targetBatch,
                    ]);
                    $createdClos++;
                }

                $sync = [];
                foreach ($sourceClo->programOutcomes as $sourcePo) {
                    $targetPo = $targetPosByCode->get(strtoupper(trim((string) $sourcePo->code)));

                    if (! $targetPo) {
                        $skippedPo++;
                        continue;
                    }

                    $sync[$targetPo->id] = ['level' => $sourcePo->pivot->level];
                }

                if ($sync) {
                    $targetClo->programOutcomes()->syncWithoutDetaching($sync);
                    $mapped += count($sync);
                }
            }
        });

        $messages = [];
        if ($createdClos > 0) {
            $messages[] = "Copied {$createdClos} CLO(s) from {$this->sourceLabel()} ({$sourceCourse->code}) into the target batch.";
        }
        if ($mapped > 0) {
            $messages[] = "Copied {$mapped} CLO→PO mapping(s) into {$targetCourse->code}.";
        } elseif ($createdClos === 0) {
            $messages[] = 'Nothing to copy — no matching CLO↔PO pairs were found.';
        }
        if ($skippedPo > 0) {
            $messages[] = "Skipped {$skippedPo} link(s) where the source PO has no same-code PO in the target.";
        }
        if ($skippedClo > 0) {
            $messages[] = "Skipped {$skippedClo} CLO(s) with no same-code CLO in the target.";
        }

        $this->parseType = ($mapped > 0 || $createdClos > 0) ? 'success' : 'info';
        $this->parseMessage = implode(' ', $messages);
    }

    private function sourceLabel(): string
    {
        $program = Program::find($this->sourceProgramId);

        return trim(($program->name ?? 'Program') . ' · Batch ' . $this->sourceBatchYear);
    }

    /**
     * The course's CLOs scoped to the selected batch: exact-batch CLOs when a
     * batch is picked, otherwise the unversioned (NULL batch) CLOs.
     */
    private function courseClos(Course $course): \Illuminate\Support\Collection
    {
        $query = $course->learningOutcomes();

        if ($this->selectedBatchYear) {
            $query->where('effective_batch_year', $this->selectedBatchYear);
        }
        // With "All Batches" selected, include every version of the course's
        // CLOs (batch-versioned and unversioned) — matching the CLO Manager.

        return $query->with('programOutcomes')->orderBy('code')->get();
    }

    private function parsePastedText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($text));
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        if (empty($lines)) {
            return [collect(), collect()];
        }

        $headerCells = preg_split('/\t/', array_shift($lines));
        // First header cell is the row label ("CLO" / course code); the rest are PO codes.
        $headerCodes = collect($headerCells)
            ->slice(1)
            ->map(fn ($c) => strtoupper(trim($c)))
            ->filter(fn ($c) => $c !== '')
            ->values();

        // Handle a pasted template where the first data row's first cell matches
        // the header label (e.g. copied the whole sheet). It's safe to ignore.
        if ($headerCodes->count() > 0 && isset($lines[0])) {
            $firstCells = preg_split('/\t/', $lines[0]);
            if (strtoupper(trim($firstCells[0] ?? '')) === strtoupper(trim($headerCells[0] ?? ''))) {
                array_shift($lines);
            }
        }

        $rows = collect();
        foreach ($lines as $i => $line) {
            $cells = preg_split('/\t/', $line);
            $cloCode = strtoupper(trim((string) ($cells[0] ?? '')));
            if ($cloCode === '') {
                continue;
            }

            // One cell per header PO, padding so trailing tabs (blank cells)
            // keep their column position.
            $levels = collect($cells)->slice(1)->values()
                ->pad($headerCodes->count(), '');

            $rows->push([
                'line' => $i + 1,
                'clo_code' => $cloCode,
                'levels' => $levels,
            ]);
        }

        return [$rows, $headerCodes];
    }

    /**
     * Accepts I/E/D (UI & Excel) plus bare G/A (internal) and fuller words.
     * Returns the stored value, or null when the cell is meant to be cleared.
     */
    private function toStoredLevel(string $raw): ?string
    {
        $v = strtolower(trim($raw));

        if ($v === '') {
            return null;
        }

        return match (true) {
            $v === 'i' || str_contains($v, 'introduc') => 'I',
            $v === 'e' || str_contains($v, 'enabl') => 'G',
            $v === 'd' || str_contains($v, 'demon') => 'A',
            $v === 'g' => 'G',
            $v === 'a' => 'A',
            default => null,
        };
    }

    private function toDisplayLevel(string $stored): string
    {
        return match ($stored) {
            'I' => 'I',
            'G' => 'E',
            'A' => 'D',
            default => '',
        };
    }

    private function courses(): \Illuminate\Support\Collection
    {
        if (! $this->selectedProgramId) {
            return collect();
        }

        return Program::findOrFail($this->selectedProgramId)
            ->courses()
            ->when($this->selectedBatchYear, function ($query) {
                $query->where('course_program.effective_batch_year', $this->selectedBatchYear);
            })
            ->orderBy('courses.code')
            ->get();
    }

    /**
     * Courses assigned to the source program + batch, so the user may pick a
     * differently-named but equivalent course to copy CLOs from.
     */
    private function sourceCourses(): \Illuminate\Support\Collection
    {
        if (! $this->sourceProgramId) {
            return collect();
        }

        return Program::findOrFail($this->sourceProgramId)
            ->courses()
            ->when($this->sourceBatchYear, function ($query) {
                $query->where('course_program.effective_batch_year', $this->sourceBatchYear);
            }, function ($query) {
                $query->whereNull('course_program.effective_batch_year');
            })
            ->orderBy('courses.code')
            ->get();
    }

    private function programOutcomes(): \Illuminate\Support\Collection
    {
        if (! $this->selectedProgramId) {
            return collect();
        }

        return ProgramOutcome::query()
            ->where('program_id', $this->selectedProgramId)
            ->when($this->selectedBatchYear, function ($query) {
                $query->where('effective_batch_year', $this->selectedBatchYear);
            }, function ($query) {
                $query->whereNull('effective_batch_year');
            })
            ->orderBy('code')
            ->get();
    }

    public function render()
    {
        $programs = Program::orderBy('name')->get();
        $batchOptions = AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year', 'desc')
            ->pluck('start_year')
            ->unique()
            ->values();
        $courses = $this->courses();

        $currentMatrix = collect();
        if ($this->selectedCourseId) {
            $course = Course::find($this->selectedCourseId);
            $clos = $this->courseClos($course);
            $programOutcomes = $this->programOutcomes();

            if ($course && $programOutcomes->isNotEmpty()) {
                foreach ($clos as $clo) {
                    $row = ['clo' => $clo, 'levels' => collect()];
                    foreach ($programOutcomes as $po) {
                        $row['levels']->push([
                            'po' => $po,
                            'stored' => (string) $clo->programOutcomes->firstWhere('id', $po->id)?->pivot?->level ?? '',
                            'display' => $this->toDisplayLevel((string) $clo->programOutcomes->firstWhere('id', $po->id)?->pivot?->level ?? ''),
                        ]);
                    }
                    $currentMatrix->push($row);
                }
            }
        }

        return view('livewire.admin.copo-mapping-paste', [
            'programs' => $programs,
            'batchOptions' => $batchOptions,
            'courses' => $courses,
            'currentMatrix' => $currentMatrix,
            'programOutcomes' => $this->selectedCourseId ? $this->programOutcomes() : collect(),
            'sourceCourses' => $this->sourceCourses(),
            'taxonomies' => BloomsTaxonomy::orderBy('domain')->orderBy('code')->get(),
        ])->extends('layouts.admin')
            ->section('content');
    }
}