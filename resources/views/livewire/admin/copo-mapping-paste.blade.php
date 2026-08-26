<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">CO-PO Mapping (Paste from Excel)</h1>
        <p class="text-sm text-gray-600">Bulk-set the CLO &rarr; PO levels for one course by copying the matrix straight from Excel.</p>
    </div>

@if($parseMessage)
    <div x-data="{ show: true }" x-show="show" x-transition
        class="fixed top-20 left-1/2 z-50 w-full max-w-xl -translate-x-1/2 px-4">
        <div class="flex items-start justify-between gap-3 rounded-xl p-4 text-sm shadow-lg border {{ $parseType === 'success' ? 'text-emerald-900 bg-emerald-50 border-emerald-200 shadow-emerald-200/50' : ($parseType === 'error' ? 'text-rose-900 bg-rose-50 border-rose-200 shadow-rose-200/50' : 'text-sky-900 bg-sky-50 border-sky-200 shadow-sky-200/50') }}">
            <div class="flex items-start gap-2">
                <i class="fas {{ $parseType === 'success' ? 'fa-circle-check text-emerald-600' : ($parseType === 'error' ? 'fa-circle-exclamation text-rose-600' : 'fa-circle-info text-sky-600') }} mt-0.5"></i>
                <span class="leading-relaxed">{{ $parseMessage }}</span>
            </div>
            <button type="button" @click="show = false" class="shrink-0 text-xs text-gray-400 hover:text-gray-600"><i class="fas fa-xmark"></i></button>
        </div>
    </div>
@endif

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Degree Program</label>
                <select wire:model.live="selectedProgramId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose a Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Batch / Cohort</label>
                <select wire:model.live="selectedBatchYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Batches</option>
                    @foreach($batchOptions as $batchOption)
                        <option value="{{ $batchOption }}">Batch {{ $batchOption }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Course</label>
                <select wire:model.live="selectedCourseId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose a Course --</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($selectedCourseId)
        {{-- Current matrix --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="text-sm font-bold text-gray-800">Current Co-PO Mapping</h3>
                <p class="mt-0.5 text-xs text-gray-500">I — Introduced, E — Enabling, D — Demonstrating</p>
            </div>
            @if($currentMatrix->isNotEmpty() && $programOutcomes->isNotEmpty())
                <div class="overflow-x-auto p-5">
                    <table class="min-w-full border-collapse text-xs">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="border-r border-gray-200 px-3 py-2 text-left font-bold text-gray-500">CLO</th>
                                @foreach($programOutcomes as $po)
                                    <th class="border-r border-gray-200 px-2 py-2 text-center font-bold text-gray-700" title="{{ $po->description }}">{{ $po->code }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($currentMatrix as $row)
                                <tr>
                                    <td class="border-r border-gray-200 px-3 py-2 align-top">
                                        <div class="flex items-center justify-between gap-2">
                                            <span class="font-bold text-indigo-700">{{ $row['clo']->code }}</span>
                                            <div class="flex shrink-0 items-center gap-1.5">
                                                <button type="button" wire:click="editClo({{ $row['clo']->id }})" class="text-[10px] font-semibold text-indigo-600 hover:text-indigo-800">Edit</button>
                                                <button type="button"
                                                    wire:click="deleteClo({{ $row['clo']->id }})"
                                                    wire:confirm="Delete CLO {{ $row['clo']->code }}? This also removes its CO-PO mappings."
                                                    class="text-[10px] font-semibold text-rose-600 hover:text-rose-800">Delete</button>
                                            </div>
                                        </div>
                                        <span class="block text-gray-500">{{ $row['clo']->description }}</span>
                                    </td>
                                    @foreach($row['levels'] as $cell)
                                        @php
                                            $style = match ($cell['stored']) {
                                                'I' => 'bg-blue-100 text-blue-800 border-blue-200',
                                                'G' => 'bg-amber-100 text-amber-800 border-amber-200',
                                                'A' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                                                default => 'text-gray-300',
                                            };
                                        @endphp
                                        <td class="border-r border-gray-200 px-2 py-2 text-center font-bold {{ $cell['display'] ? '' : '' }}">
                                            <span class="inline-flex h-7 w-7 items-center justify-center rounded border {{ $style }}">{{ $cell['display'] ?: '—' }}</span>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-8 text-center text-sm text-gray-400 italic">No CLOs or POs available to display for this course and batch.</div>
            @endif
        </div>

        {{-- Add / Edit CLO (collapsible) --}}
        <div x-data="{ open: {{ $editingCloId ? 'true' : 'false' }} }" class="bg-white rounded-lg shadow-sm border border-indigo-200 mb-6 overflow-hidden">
            <button type="button" @click="open = !open" class="w-full border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between text-left hover:bg-indigo-50/50 transition">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">{{ $editingCloId ? 'Edit CLO' : 'Add a CLO to the Course' }}</h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Add a course learning outcome for <strong>{{ $courses->firstWhere('id', $selectedCourseId)->code ?? 'this course' }}</strong>
                        · Batch {{ $selectedBatchYear }}. Click to {{ $editingCloId ? 'edit' : 'add' }}.
                    </p>
                </div>
                <i class="fas fa-chevron-down text-xs text-gray-400 transition transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open">
                <div class="p-5">
                    @if($editingCloId)
                        <button type="button" wire:click="resetCloForm" class="mb-3 text-xs font-semibold text-gray-500 hover:text-gray-700">Cancel</button>
                    @endif
                    <form wire:submit.prevent="saveClo" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">CLO Code</label>
                            <input type="text" wire:model="cloCode" placeholder="CLO-04" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                            @error('cloCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700">Description</label>
                            <input type="text" wire:model="cloDescription" placeholder="Describe the learning outcome" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                            @error('cloDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Bloom's Taxonomy</label>
                            <select wire:model="cloTaxonomyId" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                                <option value="">-- Select --</option>
                                @foreach($taxonomies as $taxonomy)
                                    <option value="{{ $taxonomy->id }}">{{ $taxonomy->code }} - {{ $taxonomy->level }}</option>
                                @endforeach
                            </select>
                            @error('cloTaxonomyId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <button type="submit" class="md:col-span-4 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            {{ $editingCloId ? 'Update CLO' : 'Add CLO' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Paste CLOs (collapsible) --}}
        <div x-data="{ open: false }" class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <button type="button" @click="open = !open" class="w-full border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between text-left hover:bg-gray-100 transition">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Add many CLOs by pasting</h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Paste rows from Excel — one CLO per line, <strong>Code</strong> then <strong>Description</strong> then
                        <strong>Bloom's Taxonomy</strong> (e.g. <code>CLO-04</code>, <code>Design database schemas</code>, <code>C1</code>).
                        Click to open.
                    </p>
                </div>
                <i class="fas fa-chevron-down text-xs text-gray-400 transition transform" :class="open ? 'rotate-180' : ''"></i>
            </button>
            <div x-show="open">
                <div class="p-5">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 mb-3">
                        <strong>Format:</strong> tab- or comma-separated, one CLO per line (the first line may be a header, which is skipped).
                        Bloom's Taxonomy accepts a code like <code>C1</code>, <code>A2</code>, <code>P1</code>, a level name like
                        <code>Remembering</code>, or leave it blank. Rows whose code already exists are updated.
                    </div>
                    <textarea wire:model="cloPasteText" rows="7" spellcheck="false"
                        placeholder="CLO-04&#09;Design database schemas&#09;C1&#10;CLO-05&#09;Implement SQL queries&#09;Applying&#10;CLO-06&#09;Evaluate system performance&#09;"
                        class="w-full font-mono rounded-md border-gray-300 text-sm shadow-sm"></textarea>
                    <div class="mt-3">
                        <button type="button" wire:click="addClosFromPaste" wire:loading.attr="disabled"
                            class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                            Add CLOs
                        </button>
                        <span wire:loading wire:target="addClosFromPaste" class="text-sm text-gray-500 ml-2">Adding…</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Copy from another program/batch --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="text-sm font-bold text-gray-800">Copy CLOs &amp; CO-PO Mapping from another Program &amp; Batch</h3>
                <p class="mt-0.5 text-xs text-gray-500">
                    Reuse CLOs (and their CLO→PO levels) from another program/batch — pick the source course, which may be
                    a differently-coded but equivalent course (e.g. "Professional Issues in Computing" vs "Social and Professional Issues").
                    Missing CLOs are created and mappings are copied for POs with a matching code.
                </p>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Source Degree Program</label>
                        <select wire:model.live="sourceProgramId" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">-- Source Program --</option>
                            @foreach($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Source Batch / Cohort</label>
                        <select wire:model.live="sourceBatchYear" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">-- Source Batch --</option>
                            @foreach($batchOptions as $batchOption)
                                <option value="{{ $batchOption }}">Batch {{ $batchOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Source Course</label>
                        <select wire:model="sourceCourseId" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                            <option value="">-- Use the target course ({!! $courses->firstWhere('id', $selectedCourseId)->code ?? '' !!}) --</option>
                            @foreach($sourceCourses as $course)
                                <option value="{{ $course->id }}">{{ $course->code }} - {{ $course->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-0.5 text-[10px] text-gray-400">Leave blank to copy from the same course in the source program.</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <button type="button" wire:click="copyFromProgramBatch" wire:loading.attr="disabled"
                        class="rounded-md bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">
                        Copy CLOs &amp; Mapping to Current Program/Batch
                    </button>
                    <span class="text-xs text-gray-500">
                        Copying into: {{ $courses->firstWhere('id', $selectedCourseId)->code ?? '' }} · {{ $programs->firstWhere('id', $selectedProgramId)->name ?? '' }} · Batch {{ $selectedBatchYear }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Paste box --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between flex-wrap gap-2">
                <div>
                    <h3 class="text-sm font-bold text-gray-800">Paste the matrix from Excel</h3>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Copy the CLO x PO cells from Excel (each row a CLO, each column a PO, cells I/E/D or blank), then paste here.
                    </p>
                </div>
                <button type="button" wire:click="generateTemplate" class="rounded-md border border-indigo-300 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100">
                    Generate Template
                </button>
            </div>
            <div class="p-5">
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-600 mb-3">
                    <strong>Format:</strong> first row is the header — an optional row label cell, then the PO codes. Each following row is a CLO code, then one cell per PO with <strong>I</strong>, <strong>E</strong>, or <strong>D</strong> (blank = no mapping). Tab-separated, one CLO per line.
                </div>
                <textarea wire:model="pastedText" rows="10" spellcheck="false"
                    placeholder="CLO&#09;PO1&#09;PO2&#09;PO3&#10;CLO-01&#09;I&#09;&#09;E&#10;CLO-02&#09;&#09;D&#09;&#10;CLO-03&#09;E&#09;D&#09;"
                    class="w-full font-mono rounded-md border-gray-300 text-sm shadow-sm"></textarea>
                <div class="mt-4 flex items-center gap-3">
                    <button type="button" wire:click="applyMapping" wire:loading.attr="disabled"
                        class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Apply Mapping
                    </button>
                    @if($applied > 0)
                        <span class="text-sm font-semibold text-emerald-700">{{ $applied }} mapping(s) applied.</span>
                    @endif
                    <span wire:loading wire:target="applyMapping" class="text-sm text-gray-500">Applying…</span>
                </div>
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-dashed border-gray-300 p-12 text-center text-gray-500">
            Select a program, batch, and course to view and edit its CO-PO mapping.
        </div>
    @endif
</div>