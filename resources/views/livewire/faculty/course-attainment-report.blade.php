<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    @php
        $course = $report['course'];
        $block = $report['block'];
        $clos = $report['clos'];
        $threshold = $report['threshold'];
        $indirectPct = $report['indirect_percentage'];
        $indirectRating = $report['indirect_rating'];
        $assessedClos = $clos->filter(fn ($c) => $c['weighted'] !== null);
        $courseAttainment = $assessedClos->isNotEmpty()
            ? round($assessedClos->avg('weighted'), 1)
            : null;
        $directAttainment = $assessedClos->filter(fn ($c) => $c['direct'] !== null)->isNotEmpty()
            ? round($assessedClos->filter(fn ($c) => $c['direct'] !== null)->avg('direct'), 1)
            : null;
        $overallAttained = $courseAttainment !== null && $courseAttainment >= $threshold;
        $submitted = $status === 'submitted';
    @endphp

    <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Course Attainment Report</h1>
            <p class="text-sm text-gray-600">{{ $course?->code }} &middot; {{ $report['sections_label'] }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($submitted)
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-100 text-indigo-800 rounded-lg text-sm font-bold">
                    <i class="fas fa-check-circle"></i>Submitted
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 text-amber-800 rounded-lg text-sm font-bold">
                    <i class="fas fa-pen"></i>Draft
                </span>
            @endif
            <a href="{{ route('attainment.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-200">
                <i class="fas fa-arrow-left"></i>Back
            </a>
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 text-white rounded-lg text-sm font-bold hover:bg-gray-800">
                <i class="fas fa-print"></i>Print
            </button>
        </div>
    </div>

    @if(session('message'))
        <div class="mb-4 bg-green-50 border border-green-300 text-green-900 px-4 py-3 rounded-lg text-sm">
            <i class="fas fa-circle-check mr-2"></i>{{ session('message') }}
        </div>
    @endif

    <div class="space-y-6">

        {{-- 1. Course Information --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden print:break-inside-avoid">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">1. Course Information</h3>
            </div>
            <div class="p-5 grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Course Code</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $course?->code ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Course Title</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $course?->name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Units</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $course?->units ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Program</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $report['program'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Section(s)</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $report['sections_label'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Batch Year</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $report['batch_year'] ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Semester / AY</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $block->semester }} / {{ $block->academicYear?->label ?? $block->academicYear?->start_year }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Instructor</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $report['faculty'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">No. of Students</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $report['students_count'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase">Attainment Threshold</p>
                    <p class="mt-1 text-sm font-bold text-gray-900">{{ $threshold }}%</p>
                </div>
            </div>
        </div>

        {{-- 2. Course Outcomes --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">2. Course Outcomes</h3>
            </div>
            <div class="p-5">
                @if($clos->isEmpty())
                    <p class="text-sm text-gray-500 italic">No course outcomes have been defined for this course.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Code</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Course Outcome</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Bloom&rsquo;s Level</th>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">Assessed Via</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($clos as $clo)
                                    <tr>
                                        <td class="px-4 py-3 text-sm font-bold text-indigo-600 whitespace-nowrap">{{ $clo['code'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-800">{{ $clo['description'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">{{ $clo['blooms'] ?? '—' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            @if($clo['items']->isEmpty())
                                                <span class="text-gray-400 italic">No assessment items mapped</span>
                                            @else
                                                <ul class="space-y-1">
                                                    @foreach($clo['items'] as $item)
                                                        <li class="flex items-center gap-2">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-700 text-xs font-semibold">{{ $item->task_type }}</span>
                                                            <span>{{ $item->task_title }}</span>
                                                            <span class="text-gray-400">&middot; {{ $item->item_name }}</span>
                                                            <span class="text-gray-400">({{ $item->max_marks }} pts)</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- 3. CO Attainment Summary --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">3. Course Outcome Attainment</h3>
            </div>
            <div class="p-5">
                <div class="mb-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Direct Attainment (80%)</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $directAttainment !== null ? $directAttainment : '—' }}%</p>
                        <p class="text-xs text-gray-500">Weighted average of assessment scores</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Indirect Attainment (20%)</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $indirectPct !== null ? $indirectPct : '—' }}%</p>
                        <p class="text-xs text-gray-500">
                            @if($indirectRating !== null)
                                Exit survey avg. {{ number_format($indirectRating, 2) }} / 5
                            @else
                                No student evaluations recorded
                            @endif
                        </p>
                    </div>
                    <div class="rounded-lg border {{ $overallAttained ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }} p-4">
                        <p class="text-xs font-semibold {{ $overallAttained ? 'text-green-700' : 'text-red-700' }} uppercase">Overall Course Attainment</p>
                        <p class="mt-1 text-2xl font-bold {{ $overallAttained ? 'text-green-800' : 'text-red-800' }}">{{ $courseAttainment !== null ? $courseAttainment : '—' }}%</p>
                        <p class="text-xs {{ $overallAttained ? 'text-green-700' : 'text-red-700' }}">
                            @if($courseAttainment !== null)
                                {{ $overallAttained ? 'Threshold achieved' : 'Below threshold' }}
                            @else
                                No CO attainment computed yet
                            @endif
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase">CO</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Direct (80%)</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Indirect (20%)</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Weighted</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Students Assessed</th>
                                <th class="px-4 py-2 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($clos as $clo)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-bold text-indigo-600">{{ $clo['code'] }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-800">{{ $clo['direct'] !== null ? $clo['direct'] : '—' }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-800">{{ $clo['indirect'] !== null ? $clo['indirect'] : '—' }}</td>
                                    <td class="px-4 py-3 text-center text-sm font-bold {{ $clo['attained'] ? 'text-green-700' : 'text-red-700' }}">{{ $clo['weighted'] !== null ? $clo['weighted'] : '—' }}</td>
                                    <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $clo['assessed'] }} / {{ $clo['total_students'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if($clo['weighted'] === null)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500 uppercase">No Data</span>
                                        @elseif($clo['attained'])
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 uppercase">Attained</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 uppercase">Not Attained</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 4. Student Performance (per CO) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">4. Student Performance per CO</h3>
            </div>
            <div class="p-5">
                @if($clos->isEmpty())
                    <p class="text-sm text-gray-500 italic">No performance data available.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-semibold text-gray-500 uppercase sticky left-0 bg-gray-50">Student</th>
                                    @foreach($clos as $clo)
                                        <th class="px-3 py-2 text-center text-xs font-semibold text-indigo-600 uppercase whitespace-nowrap">{{ $clo['code'] }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @php $firstClo = $clos->first(); @endphp
                                @foreach($firstClo['student_scores'] as $idx => $score)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-800 sticky left-0 bg-white whitespace-nowrap">
                                            <span class="text-gray-400 font-mono text-xs mr-1">{{ $score['student_number'] }}</span>{{ $score['name'] }}
                                        </td>
                                        @foreach($clos as $clo)
                                            @php $cell = $clo['student_scores'][$idx] ?? null; @endphp
                                            <td class="px-3 py-2 text-center text-sm {{ $cell && $cell['percentage'] !== null && $cell['percentage'] < $threshold ? 'text-red-600 font-bold' : 'text-gray-800' }}">
                                                {{ $cell && $cell['percentage'] !== null ? number_format($cell['percentage'], 1) . '%' : '—' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">Percentages below the {{ $threshold }}% threshold are highlighted.</p>
                @endif
            </div>
        </div>

        {{-- 5. Action Plan --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden print:break-inside-avoid">
            <div class="border-b border-gray-200 bg-gray-50 px-5 py-3 flex items-center justify-between">
                <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider">5. Action Plan / Corrective Measures</h3>
                @if(!$submitted)
                    <button type="button" wire:click="addActionPlan" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-bold hover:bg-indigo-700">
                        <i class="fas fa-plus"></i>Add Row
                    </button>
                @endif
            </div>
            <div class="p-5">
                @if($submitted)
                    <p class="text-sm text-gray-500 italic mb-4">This report has been submitted and the action plan is locked.</p>
                @endif
                @if(empty($actionPlans))
                    <p class="text-sm text-gray-500 italic">No action plans added yet.</p>
                @else
                    <div class="space-y-3">
                        @foreach($actionPlans as $index => $plan)
                            <div class="grid grid-cols-1 md:grid-cols-[1fr_1.5fr_1fr_auto] gap-3 items-start">
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Issue / Area</label>
                                    <input type="text" wire:model="actionPlans.{{ $index }}.issue" {{ $submitted ? 'disabled' : '' }}
                                           placeholder="e.g. CO2 not attained" class="mt-1 w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Corrective Action</label>
                                    <input type="text" wire:model="actionPlans.{{ $index }}.action" {{ $submitted ? 'disabled' : '' }}
                                           placeholder="What will be done differently" class="mt-1 w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div>
                                    <label class="text-xs font-semibold text-gray-500 uppercase">Target Date</label>
                                    <input type="date" wire:model="actionPlans.{{ $index }}.target_date" {{ $submitted ? 'disabled' : '' }}
                                           class="mt-1 w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                                <div class="pt-6">
                                    @if(!$submitted)
                                        <button type="button" wire:click="removeActionPlan({{ $index }})" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-4">
                    @if(!$submitted)
                        <button type="button" wire:click="saveDraft" wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-gray-700 text-white rounded-lg text-sm font-bold hover:bg-gray-800">
                            <i class="fas fa-save"></i>Save Draft
                        </button>
                        <button type="button" wire:click="submitReport" wire:confirm="Submit this Course Attainment Report for review?"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-2 px-5 py-2 bg-indigo-600 text-white rounded-lg text-sm font-bold hover:bg-indigo-700">
                            <i class="fas fa-paper-plane"></i>Submit Report
                        </button>
                    @else
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-50 text-indigo-700 rounded-lg text-sm font-bold">
                            <i class="fas fa-check-circle"></i>Submitted {{ optional($submittedAt)->format('M d, Y h:i A') }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

    </div>
</div>
