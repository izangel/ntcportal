<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">CLO Manager</h1>
        <p class="text-sm text-gray-600">View and manage the Course Learning Outcomes of every course assigned to a program.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-rose-800 bg-rose-100 rounded-lg border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        </div>
    </div>

    @if($selectedProgramId)
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Courses + CLOs --}}
            <div class="lg:col-span-2 space-y-4">
                @forelse($assignedCourses as $course)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" wire:key="course-{{ $course->id }}">
                        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div>
                                <div class="font-bold text-gray-900">{{ $course->code }}</div>
                                <div class="text-sm text-gray-600">{{ $course->name }}</div>
                            </div>
                            <button type="button" wire:click="beginCloCreate({{ $course->id }})" class="shrink-0 rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700">
                                + Add CLO
                            </button>
                        </div>
                        <div class="divide-y divide-gray-100">
                            @forelse($course->learningOutcomes as $clo)
                                <div class="px-5 py-3 flex items-start justify-between gap-3 hover:bg-gray-50/50" wire:key="clo-{{ $clo->id }}">
                                    <div class="text-sm min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-indigo-700">{{ $clo->code }}</span>
                                            @if($clo->bloomsTaxonomy)
                                                <span class="inline-block rounded bg-gray-100 px-1.5 py-0.5 text-[10px] font-semibold text-gray-600">
                                                    {{ $clo->bloomsTaxonomy->code }}: {{ $clo->bloomsTaxonomy->level }}
                                                </span>
                                            @endif
                                            @php
                                                $poMapped = $clo->programOutcomes->isNotEmpty();
                                            @endphp
                                            <span class="inline-block rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $poMapped ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-600' }}">
                                                {{ $poMapped ? $clo->programOutcomes->count() . ' PO(s) mapped' : 'No PO mapped' }}
                                            </span>
                                        </div>
                                        <p class="mt-0.5 text-gray-700 leading-relaxed">{{ $clo->description }}</p>
                                    </div>
                                    <div class="flex shrink-0 items-center gap-3 text-xs font-semibold">
                                        <button type="button" wire:click="editClo({{ $clo->id }})" class="text-indigo-600 hover:text-indigo-800">Edit</button>
                                        <button type="button"
                                            wire:click="deleteClo({{ $clo->id }})"
                                            wire:confirm="Delete CLO {{ $clo->code }}? This also removes its CO-PO mappings, assessment items, and any recorded student marks for those items."
                                            class="text-rose-600 hover:text-rose-800">Delete</button>
                                    </div>
                                </div>
                            @empty
                                <div class="px-5 py-4 text-sm italic text-gray-400">No CLOs assigned to this course{{ $selectedBatchYear ? ' for this batch' : '' }}.</div>
                            @endforelse
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-lg shadow-sm border border-dashed border-gray-300 p-10 text-center text-sm text-gray-500">
                        No courses assigned to this program{{ $selectedBatchYear ? ' for the selected batch' : '' }}. Assign courses using the Program Course Manager first.
                    </div>
                @endforelse
            </div>

            {{-- CLO form --}}
            <div class="lg:col-span-1 space-y-4">
                @if($cloCourseId)
                    <div class="bg-white rounded-lg shadow-sm border border-indigo-200 p-5 sticky top-4" wire:key="clo-form-{{ $editingCloId ?? 'new' }}">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-bold text-gray-900">{{ $editingCloId ? 'Edit CLO' : 'Add CLO to Course' }}</h3>
                            <button type="button" wire:click="resetCloForm" class="text-xs font-semibold text-gray-500 hover:text-gray-700">Cancel</button>
                        </div>

                        <form wire:submit.prevent="saveClo" class="space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700">Course</label>
                                <div class="mt-1 rounded-md bg-gray-50 border border-gray-200 px-3 py-2 text-xs font-semibold text-gray-800">
                                    {{ $assignedCourses->firstWhere('id', $cloCourseId)?->code ?? $courses->firstWhere('id', $cloCourseId)?->code ?? 'Course' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700">CLO Code</label>
                                <input type="text" wire:model="cloCode" placeholder="CLO-01" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                                @error('cloCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700">Description</label>
                                <textarea wire:model="cloDescription" rows="4" placeholder="Describe the learning outcome" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">{{ $cloDescription }}</textarea>
                                @error('cloDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700">Bloom's Taxonomy Level</label>
                                <select wire:model="cloTaxonomyId" class="mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm">
                                    <option value="">-- Select --</option>
                                    @foreach($taxonomies as $taxonomy)
                                        <option value="{{ $taxonomy->id }}">{{ $taxonomy->code }} - {{ $taxonomy->level }}</option>
                                    @endforeach
                                </select>
                                @error('cloTaxonomyId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div class="text-xs text-gray-600">
                                @if($selectedBatchYear)
                                    Batch: <strong>{{ $selectedBatchYear }}</strong>
                                @else
                                    Applies to <strong>all batches</strong> (unversioned).
                                @endif
                            </div>

                            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                {{ $editingCloId ? 'Update CLO' : 'Save CLO' }}
                            </button>
                        </form>
                    </div>
                @else
                    <div class="bg-gray-50 rounded-lg border border-dashed border-gray-300 p-8 text-center text-sm text-gray-500">
                        Click <strong>+ Add CLO</strong> on a course to create a learning outcome, or <strong>Edit</strong> an existing one.
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-dashed border-gray-300 p-12 text-center text-gray-500">
            Select a degree program to view and manage its course learning outcomes.
        </div>
    @endif
</div>