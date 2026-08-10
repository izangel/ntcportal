@extends('layouts.admin')

@section('content')
<div class="max-w-full mx-auto py-3 px-4 bg-[#F8FAFC] min-h-screen">

    @php $totalCols = 7; @endphp

    <div class="flex flex-wrap justify-between items-end mb-4 px-1 gap-4">
        <div class="space-y-2">
            <h2 class="text-xl font-bold text-slate-500 tracking-tight">Academic Block Management</h2>

            <form action="{{ route('course_blocks.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                <select name="level" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white focus:ring-[#BEE3F8]">
                    <option value="">LEVELS</option>
                    <option value="COLLEGE" {{ request('level') == 'COLLEGE' ? 'selected' : '' }}>COLLEGE</option>
                    <option value="SHS" {{ request('level') == 'SHS' ? 'selected' : '' }}>SHS</option>
                </select>

                <select name="ay" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white">
                    <option value="">ACAD YEAR</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}" {{ $selAy == $ay->id ? 'selected' : '' }}>{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                    @endforeach
                </select>

                <select name="sem" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white">
                    <option value="">SEMESTER</option>
                    <option value="1st" {{ $selSem == '1st' ? 'selected' : '' }}>1st Semester</option>
                    <option value="2nd" {{ $selSem == '2nd' ? 'selected' : '' }}>2nd Semester</option>
                    <option value="Summer" {{ $selSem == 'Summer' ? 'selected' : '' }}>Summer</option>
                </select>

                <input type="hidden" name="view_by" value="{{ $viewBy }}">

                {{-- View Mode Toggle: Program-Section | Teacher --}}
                <div class="inline-flex rounded-md shadow-sm border border-[#E2E8F0] overflow-hidden" role="group">
                    <a href="{{ route('course_blocks.index', array_merge(request()->except(['page', 'view_by']), ['view_by' => 'section'])) }}"
                       class="px-4 py-1 text-[11px] font-bold uppercase tracking-wider transition {{ $viewBy === 'section' ? 'bg-[#3182CE] text-white' : 'bg-white text-slate-400 hover:bg-gray-50' }}">
                        Prog-Section
                    </a>
                    <a href="{{ route('course_blocks.index', array_merge(request()->except(['page', 'view_by']), ['view_by' => 'faculty'])) }}"
                       class="px-4 py-1 text-[11px] font-bold uppercase tracking-wider border-l border-[#E2E8F0] transition {{ $viewBy === 'faculty' ? 'bg-[#3182CE] text-white' : 'bg-white text-slate-400 hover:bg-gray-50' }}">
                        Teacher
                    </a>
                </div>

                @if(request()->anyFilled(['level', 'ay', 'sem']))
                    <a href="{{ route('course_blocks.index') }}" class="text-[10px] text-rose-300 self-center hover:underline uppercase font-bold">Clear</a>
                @endif
            </form>
        </div>

        <div class="flex items-center gap-3">
            @if($defaultAyId && ($selAy == $defaultAyId) && ($selSem == $defaultSemKey))
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-full text-[10px] font-bold uppercase tracking-wider">
                    <i class="fas fa-circle-check"></i> Active Term
                </span>
            @endif
            <a href="{{ route('course_blocks.create') }}" class="bg-[#C6F6D5] hover:bg-[#9ae6b4] text-[#2F855A] text-[10px] font-bold py-2 px-4 rounded shadow-sm uppercase transition">
                + New Block
            </a>
        </div>
    </div>

    <div class="bg-white border border-[#E2E8F0] rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full table-auto">
            <thead class="bg-[#F0FFF4] border-b border-[#C6F6D5]">
                <tr>
                    <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase w-20">Code</th>
                    <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase">Description</th>
                    <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase w-36">Schedule</th>
                    <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase w-16">Room</th>
                    <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase">Faculty</th>
                    <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase">Term & AY</th>
                    <th class="px-3 py-2 text-right text-[9px] font-bold text-[#2F855A] uppercase w-24">Actions</th>
                </tr>
            </thead>
            <tbody class="text-[11px] divide-y divide-[#EDF2F7]">

                @if($viewBy === 'section')

                    {{-- ============ PROGRAM-SECTION VIEW: grouped per section ============ --}}
                    @forelse($groupedBlocks as $group)
                        <tr class="bg-[#EBF8FF] border-y-2 border-[#BEE3F8]">
                            <td colspan="{{ $totalCols }}" class="px-3 py-1.5 text-[10px] font-black text-[#2B6CB0] uppercase tracking-widest">
                                <i class="fas fa-layer-group mr-2"></i>{{ $group['label'] }}
                                <span class="text-[#63B3ED] normal-case font-bold">({{ count($group['blocks']) }} block{{ count($group['blocks']) !== 1 ? 's' : '' }})</span>
                            </td>
                        </tr>
                        @foreach($group['blocks'] as $block)
                            @include('course_blocks.partials.row')
                        @endforeach
                    @empty
                        <tr><td colspan="{{ $totalCols }}" class="px-6 py-12 text-center text-slate-300 italic">No course blocks found.</td></tr>
                    @endforelse

                @else

                    {{-- ============ TEACHER VIEW: grouped per faculty ============ --}}
                    @php $lastFacultyId = null; @endphp
                    @forelse($courseBlocks as $block)
                        @if($lastFacultyId !== null && $lastFacultyId !== $block->faculty_id)
                            @php $teacher = $block->faculty; @endphp
                            <tr class="bg-[#EBF8FF] border-y-2 border-[#BEE3F8]">
                                <td colspan="{{ $totalCols }}" class="px-3 py-1.5 text-[10px] font-black text-[#2B6CB0] uppercase tracking-widest">
                                    <i class="fas fa-chalkboard-user mr-2"></i>{{ $teacher->last_name ?? 'N/A' }}, {{ substr($teacher->first_name ?? 'N/A', 0, 1) }}.
                                </td>
                            </tr>
                        @elseif($lastFacultyId === null)
                            @php $teacher = $block->faculty; @endphp
                            <tr class="bg-[#EBF8FF] border-y-2 border-[#BEE3F8]">
                                <td colspan="{{ $totalCols }}" class="px-3 py-1.5 text-[10px] font-black text-[#2B6CB0] uppercase tracking-widest">
                                    <i class="fas fa-chalkboard-user mr-2"></i>{{ $teacher->last_name ?? 'N/A' }}, {{ substr($teacher->first_name ?? 'N/A', 0, 1) }}.
                                </td>
                            </tr>
                        @endif

                        @include('course_blocks.partials.row')

                        @php $lastFacultyId = $block->faculty_id; @endphp
                    @empty
                        <tr><td colspan="{{ $totalCols }}" class="px-6 py-12 text-center text-slate-300 italic">No course blocks found.</td></tr>
                    @endforelse

                    <div class="px-4 py-1.5 bg-[#F7FAFC] border-t border-[#EDF2F7]">
                        <div class="scale-90 origin-left">
                            {{ $courseBlocks->links() }}
                        </div>
                    </div>

                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection