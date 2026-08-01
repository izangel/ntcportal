<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Course Materials</h1>
        <p class="text-sm text-gray-600">Share links for each class. Upload your files to Google Drive, then paste the share links here — students will see them instantly.</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg border border-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-rose-800 bg-rose-100 rounded-lg border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Year</label>
                <select wire:model.live="academicYearId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select wire:model.live="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-1">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class / Course Block</label>
                <select wire:model.live="selectedBlockId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose a class --</option>
                    @foreach($assignedBlocks as $block)
                        <option value="{{ $block['id'] }}">
                            {{ $block['course_code'] }} - {{ $block['course_name'] }} ({{ $block['sections'] }}) {{ $block['schedule_string'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    @if($selectedBlockId)
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            @php
                $typeMeta = [
                    'lms' => ['label' => 'LMS Link', 'icon' => 'fa-graduation-cap', 'hint' => 'Learning management system (e.g. Google Classroom, Canvas)'],
                    'course_pack' => ['label' => 'Course Pack', 'icon' => 'fa-folder-open', 'hint' => 'Compiled readings, handouts and activities'],
                    'syllabus' => ['label' => 'Syllabus', 'icon' => 'fa-file-lines', 'hint' => 'Course syllabus / outline'],
                ];
                $typeColor = [
                    'lms' => 'bg-indigo-50 text-indigo-700 border-indigo-100',
                    'course_pack' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                    'syllabus' => 'bg-amber-50 text-amber-700 border-amber-100',
                ];
            @endphp

            @foreach(['lms', 'course_pack', 'syllabus'] as $type)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg {{ $typeColor[$type] }}">
                                <i class="fas {{ $typeMeta[$type]['icon'] }}"></i>
                            </span>
                            <div>
                                <h2 class="text-sm font-bold text-gray-800">{{ $typeMeta[$type]['label'] }}</h2>
                                <p class="text-[11px] text-gray-400">{{ $typeMeta[$type]['hint'] }}</p>
                            </div>
                        </div>
                        <button wire:click="startAdd('{{ $type }}')" class="shrink-0 inline-flex items-center px-2.5 py-1.5 bg-gray-50 text-gray-600 text-xs font-semibold rounded-lg hover:bg-indigo-50 hover:text-indigo-700">
                            <i class="fas fa-plus mr-1"></i> Add
                        </button>
                    </div>

                    @if(count($materials[$type] ?? []))
                        <ul class="space-y-2">
                            @foreach($materials[$type] as $material)
                                <li class="group flex items-center gap-2 p-2.5 rounded-lg border border-gray-100 hover:border-indigo-100 hover:bg-indigo-50/40">
                                    <i class="fas fa-link text-xs text-gray-400"></i>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-semibold text-gray-800 truncate">{{ $material['title'] }}</p>
                                        <p class="text-[11px] text-gray-400 truncate">{{ $material['url'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <a href="{{ $material['url'] }}" target="_blank" rel="noopener" class="p-1.5 text-gray-400 hover:text-indigo-600" title="Open link">
                                            <i class="fas fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                        <button wire:click="editMaterial({{ $material['id'] }})" class="p-1.5 text-gray-400 hover:text-amber-600" title="Edit">
                                            <i class="fas fa-pen text-xs"></i>
                                        </button>
                                        <button wire:click="deleteMaterial({{ $material['id'] }})" wire:confirm="Remove this material link?" class="p-1.5 text-gray-400 hover:text-rose-600" title="Delete">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-xs text-gray-400 italic py-4 text-center">No {{ strtolower($typeMeta[$type]['label']) }} yet.</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4">
                {{ $editingId ? 'Edit Material Link' : 'Add Material Link' }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Type</label>
                    <select wire:model="formType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['lms' => 'LMS Link', 'course_pack' => 'Course Pack', 'syllabus' => 'Syllabus'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                    <input type="text" wire:model="formTitle" placeholder="e.g. LMS - FABM 2 Section A" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('formTitle') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Google Drive Link</label>
                    <input type="url" wire:model="formUrl" placeholder="https://drive.google.com/..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('formUrl') <span class="text-xs text-rose-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4 flex items-center gap-2">
                <button wire:click="saveMaterial" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                    <i class="fas fa-save mr-2"></i> {{ $editingId ? 'Update Link' : 'Save Link' }}
                </button>
                @if($editingId)
                    <button wire:click="resetForm" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Select a class above to manage its material links.</p>
        </div>
    @endif
</div>
