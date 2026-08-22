<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Course;
use App\Models\AcademicYear;
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

    public $pastedText = '';
    public $parseMessage = null;
    public $parseType = 'info';
    public $applied = 0;

    public function updatedSelectedProgramId()
    {
        $this->reset(['selectedCourseId', 'pastedText', 'parseMessage', 'parseType', 'applied']);
    }

    public function updatedSelectedBatchYear()
    {
        $this->reset(['selectedCourseId', 'pastedText', 'parseMessage', 'parseType', 'applied']);
    }

    public function updatedSelectedCourseId()
    {
        $this->reset(['pastedText', 'parseMessage', 'parseType', 'applied']);
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
     * The course's CLOs scoped to the selected batch: exact-batch CLOs when a
     * batch is picked, otherwise the unversioned (NULL batch) CLOs.
     */
    private function courseClos(Course $course): \Illuminate\Support\Collection
    {
        $query = $course->learningOutcomes();

        if ($this->selectedBatchYear) {
            $query->where('effective_batch_year', $this->selectedBatchYear);
        } else {
            $query->whereNull('effective_batch_year');
        }

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
        ])->extends('layouts.admin')
            ->section('content');
    }
}