<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Program;
use App\Models\Peo;
use App\Models\ProgramOutcome;
use App\Models\InstitutionalGoal;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class ObeSetup extends Component
{
    public $selectedProgramId = null;
    public $selectedBatchYear = ''; // Active batch filter for listing
    public $activeTab = 'peo'; // 'peo' or 'po'

    // Form inputs for PEO
    public $peoId = null;
    public $peoCode = '', $peoDescription = '', $peoEffectiveBatch = '', $selectedGoals = [];

    // Form inputs for PO
    public $poId = null;
    public $poCode = '', $poDescription = '', $poEffectiveBatch = '', $selectedPeos = [];

    public function updatedSelectedProgramId()
    {
        $this->cancelEdit();
    }

    public function updatedSelectedBatchYear()
    {
        $this->cancelEdit();

        $this->peoEffectiveBatch = $this->selectedBatchYear;
        $this->poEffectiveBatch = $this->selectedBatchYear;
    }

    public function updatedActiveTab()
    {
        $this->cancelEdit();
    }

    // ==========================================
    // PEO ACTIONS
    // ==========================================

    // ==========================================
// PEO ACTIONS
// ==========================================

public function editPeo($id)
{
    $peo = Peo::with('institutionalGoals')->findOrFail($id);

    // Populate properties
    $this->peoId             = $peo->id;
    $this->peoCode           = $peo->code;
    $this->peoDescription    = $peo->description;
    $this->peoEffectiveBatch = $peo->effective_batch_year ?? '';
    
    // Cast IDs to strings for checkbox arrays in Livewire
    $this->selectedGoals     = $peo->institutionalGoals
        ->pluck('id')
        ->map(fn($goalId) => (string) $goalId)
        ->toArray();
}

public function savePeo()
{
    $this->validate([
        'selectedProgramId' => 'required|exists:programs,id',
        'peoCode'           => 'required|string|max:20',
        'peoDescription'    => 'required|string',
        'peoEffectiveBatch' => 'nullable|string|max:20',
        'selectedGoals'     => 'array',
    ]);

    $data = [
        'program_id'           => $this->selectedProgramId,
        'code'                 => $this->peoCode,
        'description'          => $this->peoDescription,
        'effective_batch_year' => $this->peoEffectiveBatch ?: ($this->selectedBatchYear ?: null),
    ];

    if ($this->peoId) {
        // UPDATE existing record
        $peo = Peo::findOrFail($this->peoId);
        $peo->update($data);
        $message = 'PEO updated successfully.';
    } else {
        // CREATE new record
        $peo = Peo::create($data);
        $message = 'PEO created successfully.';
    }

    // Sync pivot table
    $peo->institutionalGoals()->sync($this->selectedGoals);

    $this->cancelEdit();
    session()->flash('message', $message);
}

// ==========================================
// PO ACTIONS
// ==========================================

public function editPo($id)
{
    // 1. Fetch PO with relationships
    $po = ProgramOutcome::with('peos')->findOrFail($id);

    // 2. Clear state first to ensure clean property assignment
    $this->resetErrorBag();

    // 3. Assign scalar properties directly
    $this->poId             = $po->id;
    $this->poCode           = (string) $po->code;
    $this->poDescription    = (string) $po->description;
    $this->poEffectiveBatch = (string) ($po->effective_batch_year ?? '');

    // 4. Force string types for array bindings so Blade checkboxes match
    $this->selectedPeos     = $po->peos
        ->pluck('id')
        ->map(fn ($peoId) => (string) $peoId)
        ->toArray();
}

public function savePo()
{
    $this->validate([
        'selectedProgramId' => 'required|exists:programs,id',
        'poCode'           => 'required|string|max:20',
        'poDescription'    => 'required|string',
        'poEffectiveBatch' => 'nullable|string|max:20',
        'selectedPeos'     => 'array',
    ]);

    $data = [
        'program_id'           => $this->selectedProgramId,
        'code'                 => $this->poCode,
        'description'          => $this->poDescription,
        'effective_batch_year' => $this->poEffectiveBatch ?: ($this->selectedBatchYear ?: null),
    ];

    if ($this->poId) {
        // UPDATE existing record
        $po = ProgramOutcome::findOrFail($this->poId);
        $po->update($data);
        $message = 'Program Outcome updated successfully.';
    } else {
        // CREATE new record
        $po = ProgramOutcome::create($data);
        $message = 'Program Outcome created successfully.';
    }

    // Sync pivot table
    $po->peos()->sync($this->selectedPeos);

    $this->cancelEdit();
    session()->flash('message', $message);
}

    public function deletePo($id)
    {
        $po = ProgramOutcome::findOrFail($id);
        $po->peos()->detach();
        $po->delete();

        if ($this->poId === $id) {
            $this->cancelEdit();
        }

        session()->flash('message', 'Program Outcome deleted successfully.');
    }

    public function cancelEdit()
    {
        $this->reset([
            'peoId', 'peoCode', 'peoDescription', 'peoEffectiveBatch', 'selectedGoals',
            'poId', 'poCode', 'poDescription', 'poEffectiveBatch', 'selectedPeos'
        ]);
    }

    // ==========================================
    // COPY FROM PREVIOUS BATCH
    // ==========================================

    public function carryForwardPeosFromPreviousBatch(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedBatchYear' => 'required',
        ]);

        $programId = (int) $this->selectedProgramId;
        $newBatch = (string) $this->selectedBatchYear;

        $previousBatch = $this->previousBatchWith('peos');

        if (!$previousBatch) {
            session()->flash('message', 'No earlier batch with PEOs found to carry forward from.');
            return;
        }

        $previousPeos = Peo::with('institutionalGoals')
            ->where('program_id', $programId)
            ->where('effective_batch_year', $previousBatch)
            ->get();

        if ($previousPeos->isEmpty()) {
            session()->flash('message', "No PEOs found for batch {$previousBatch}.");
            return;
        }

        DB::transaction(function () use ($programId, $newBatch, $previousPeos) {
            foreach ($previousPeos as $peo) {
                $newPeo = Peo::create([
                    'program_id' => $programId,
                    'code' => $peo->code,
                    'description' => $peo->description,
                    'effective_batch_year' => $newBatch,
                ]);

                $newPeo->institutionalGoals()->sync($peo->institutionalGoals->pluck('id'));
            }
        });

        session()->flash('message', "{$previousPeos->count()} PEO(s) carried forward from batch {$previousBatch} to batch {$newBatch}, including their Institutional Goal mappings.");
    }

    public function carryForwardPosFromPreviousBatch(): void
    {
        $this->validate([
            'selectedProgramId' => 'required|exists:programs,id',
            'selectedBatchYear' => 'required',
        ]);

        $programId = (int) $this->selectedProgramId;
        $newBatch = (string) $this->selectedBatchYear;

        $previousBatch = $this->previousBatchWith('pos');

        if (!$previousBatch) {
            session()->flash('message', 'No earlier batch with POs found to carry forward from.');
            return;
        }

        $previousPos = ProgramOutcome::with('peos')
            ->where('program_id', $programId)
            ->where('effective_batch_year', $previousBatch)
            ->get();

        if ($previousPos->isEmpty()) {
            session()->flash('message', "No POs found for batch {$previousBatch}.");
            return;
        }

        $newPeosByCode = Peo::where('program_id', $programId)
            ->where('effective_batch_year', $newBatch)
            ->get()
            ->keyBy('code');

        DB::transaction(function () use ($programId, $newBatch, $previousPos, $newPeosByCode) {
            foreach ($previousPos as $po) {
                $newPo = ProgramOutcome::create([
                    'program_id' => $programId,
                    'code' => $po->code,
                    'description' => $po->description,
                    'effective_batch_year' => $newBatch,
                ]);

                $newPeoIds = $po->peos
                    ->map(fn ($peo) => $newPeosByCode->get($peo->code)?->id)
                    ->filter()
                    ->values();

                $newPo->peos()->sync($newPeoIds);
            }
        });

        session()->flash('message', "{$previousPos->count()} PO(s) carried forward from batch {$previousBatch} to batch {$newBatch}. PO-to-PEO links were re-mapped to the matching PEOs of batch {$newBatch}.");
    }

    private function previousBatchWith(string $entity): ?string
    {
        if (!$this->selectedProgramId || !$this->selectedBatchYear) {
            return null;
        }

        $table = $entity === 'pos' ? 'program_outcomes' : 'peos';

        return DB::table($table)
            ->where('program_id', (int) $this->selectedProgramId)
            ->where('effective_batch_year', '<', (string) $this->selectedBatchYear)
            ->orderByDesc('effective_batch_year')
            ->value('effective_batch_year');
    }

    public function render()
    {
        $peosQuery = Peo::query();
        if ($this->selectedProgramId) {
            $peosQuery->where('program_id', $this->selectedProgramId);
            if ($this->selectedBatchYear !== '') {
                $peosQuery->where('effective_batch_year', $this->selectedBatchYear);
            }
        }
        $peos = $this->selectedProgramId ? $peosQuery->with('institutionalGoals')->get() : collect();

        $posQuery = ProgramOutcome::query();
        if ($this->selectedProgramId) {
            $posQuery->where('program_id', $this->selectedProgramId);
            if ($this->selectedBatchYear !== '') {
                $posQuery->where('effective_batch_year', $this->selectedBatchYear);
            }
        }
        $pos = $this->selectedProgramId ? $posQuery->with('peos')->get() : collect();

        $batchOptions = AcademicYear::query()
            ->whereNotNull('start_year')
            ->orderBy('start_year', 'desc')
            ->pluck('start_year')
            ->map(fn ($year) => (string) $year)
            ->unique()
            ->values();

        return view('livewire.admin.obe-setup', [
            'programs'           => Program::all(),
            'institutionalGoals' => InstitutionalGoal::where('is_active', true)->get(),
            'peos'               => $peos,
            'pos'                => $pos,
            'batchOptions'       => $batchOptions,
            'previousBatchWithPeos' => $this->previousBatchWith('peos'),
            'previousBatchWithPos'  => $this->previousBatchWith('pos'),
        ])->extends('layouts.admin')
          ->section('content');
    }
}