<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Student Masterlist</h1>
        <p class="text-sm text-gray-600">Every student in the registry, with duplicate records flagged by email, school ID, or full name.</p>
    </div>

    @if (session()->has('success'))
        <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg border border-emerald-200">{{ session('success') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-rose-800 bg-rose-100 rounded-lg border border-rose-200">{{ session('error') }}</div>
    @endif

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Total Students</p>
            <p class="text-2xl font-black text-gray-900">{{ $totals['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border {{ $totals['duplicates'] > 0 ? 'border-rose-200' : 'border-gray-200' }} p-4">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Flagged Duplicates</p>
            <p class="text-2xl font-black {{ $totals['duplicates'] > 0 ? 'text-rose-600' : 'text-gray-900' }}">{{ $totals['duplicates'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">By Email</p>
            <p class="text-2xl font-black text-indigo-600">{{ $totals['by_email'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">By School ID</p>
            <p class="text-2xl font-black text-amber-600">{{ $totals['by_student_id'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">By Full Name</p>
            <p class="text-2xl font-black text-blue-600">{{ $totals['by_name'] }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6 flex flex-col md:flex-row md:items-center gap-3">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="q" placeholder="Search name, ID, or email..."
                class="w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
            <input type="checkbox" wire:model.live="showDuplicatesOnly" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Duplicates only
        </label>
        <select wire:model.live="duplicateType" class="rounded-md border-gray-300 shadow-sm text-sm">
            <option value="all">All duplicate reasons</option>
            <option value="email">Email</option>
            <option value="student_id">School ID</option>
            <option value="name">Full name</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto max-h-[70vh] overflow-y-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-[10px] uppercase tracking-wider text-gray-500 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold">Flag</th>
                        <th class="px-4 py-3 text-left font-bold">Student ID</th>
                        <th class="px-4 py-3 text-left font-bold">Full Name</th>
                        <th class="px-4 py-3 text-left font-bold">Email</th>
                        <th class="px-4 py-3 text-left font-bold">Gender</th>
                        <th class="px-4 py-3 text-left font-bold">Birthday</th>
                        <th class="px-4 py-3 text-left font-bold">Program(s)</th>
                        <th class="px-4 py-3 text-left font-bold">Section(s)</th>
                        <th class="px-4 py-3 text-left font-bold">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($students as $student)
                        <tr class="{{ $student->is_duplicate ? 'bg-rose-50/60' : 'hover:bg-gray-50' }}">
                            <td class="px-4 py-2.5">
                                @if($student->is_duplicate)
                                    <div class="flex flex-col gap-0.5">
                                        @foreach($student->duplicate_flags as $flag)
                                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold
                                                {{ $flag['type'] === 'email' ? 'bg-indigo-100 text-indigo-700' : ($flag['type'] === 'student_id' ? 'bg-amber-100 text-amber-700' : 'bg-sky-100 text-sky-700') }}"
                                                title="Duplicates: #{{ $flag['matches']->implode(', #') }}">
                                                <i class="fas fa-triangle-exclamation"></i>
                                                {{ $flag['type'] === 'student_id' ? 'ID' : ucfirst($flag['type']) }} ({{ $flag['matches']->count() }})
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700"><i class="fas fa-circle-check mr-1"></i>OK</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 font-mono text-xs {{ $student->student_id ? 'text-gray-700' : 'text-gray-400 italic' }}">{{ $student->student_id ?: '—' }}</td>
                            <td class="px-4 py-2.5 font-semibold text-gray-900">{{ $student->last_name }}, {{ $student->first_name }}@if($student->middle_name) {{ $student->middle_name }}@endif</td>
                            <td class="px-4 py-2.5 text-gray-700">{{ $student->email ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $student->gender ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $student->birthday?->format('Y-m-d') ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $student->programs ?: '—' }}</td>
                            <td class="px-4 py-2.5 text-gray-600">{{ $student->section_names ?: '—' }}</td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <button type="button" wire:click="editStudent({{ $student->id }})" class="rounded bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-600 hover:bg-indigo-100">Edit</button>
                                    <button type="button"
                                        wire:click="deleteStudent({{ $student->id }})"
                                        wire:confirm="Delete student {{ $student->last_name }}, {{ $student->first_name }}? Only allowed if no other records (enrollments, sections, marks, evaluations) reference this student."
                                        class="rounded bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-100">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400 italic">No students match the current filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 text-xs text-gray-500">
            Showing <strong>{{ $students->count() }}</strong> of {{ $totals['total'] }} students.
            Flagged rows share an email, school ID, or full name with another record — hover a badge to see the matching row IDs.
        </div>
    </div>
</div>