<x-app-layout>
    <div class="max-w-full mx-auto py-3 px-4 bg-[#F8FAFC] min-h-screen">
        
        <div class="flex flex-wrap justify-between items-end mb-4 px-1 gap-4">
            <div class="space-y-2">
                <h2 class="text-xl font-bold text-slate-500 tracking-tight">Academic Block Management</h2>
                
                {{-- RESTORED DROPDOWNS --}}
                <form action="{{ route('course_blocks.index') }}" method="GET" class="flex flex-wrap gap-2">
                    <select name="level" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white focus:ring-[#BEE3F8]">
                        <option value="">LEVELS</option>
                        <option value="COLLEGE" {{ request('level') == 'COLLEGE' ? 'selected' : '' }}>COLLEGE</option>
                        <option value="SHS" {{ request('level') == 'SHS' ? 'selected' : '' }}>SHS</option>
                    </select>

                    <select name="ay" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white">
                        <option value="">ACAD YEAR</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ request('ay') == $ay->id ? 'selected' : '' }}>{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                        @endforeach
                    </select>

                    <select name="sem" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white">
                        <option value="">SEMESTER</option>
                        <option value="1st" {{ request('sem') == '1st' ? 'selected' : '' }}>1st Semester</option>
                        <option value="2nd" {{ request('sem') == '2nd' ? 'selected' : '' }}>2nd Semester</option>
                        <option value="Summer" {{ request('sem') == 'Summer' ? 'selected' : '' }}>Summer</option>
                    </select>

                    <select name="sort" onchange="this.form.submit()" class="text-[11px] py-1 border-[#E2E8F0] text-slate-500 rounded bg-white focus:ring-[#BEE3F8]">
                        <option value="">SORT BY</option>
                        <option value="faculty" {{ request('sort') == 'faculty' ? 'selected' : '' }}>FACULTY (A-Z)</option>
                    </select>

                    

                    @if(request()->anyFilled(['level', 'ay', 'sem', 'sort']))
                        <a href="{{ route('course_blocks.index') }}" class="text-[10px] text-rose-300 self-center hover:underline uppercase font-bold">Clear</a>
                    @endif
                </form>
            </div>

            <a href="{{ route('course_blocks.create') }}" class="bg-[#C6F6D5] hover:bg-[#9ae6b4] text-[#2F855A] text-[10px] font-bold py-2 px-4 rounded shadow-sm uppercase transition">
                + New Block
            </a>
        </div>

        <div class="bg-white border border-[#E2E8F0] rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full table-auto">
                <thead class="bg-[#F0FFF4] border-b border-[#C6F6D5]">
                    <tr>
                        <th class="px-3 py-2 text-left text-[9px] font-bold text-[#2F855A] uppercase tracking-widest">Prog-Section</th>
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
                    @php $lastFacultyId = null; @endphp
                    
                    @forelse($courseBlocks as $block)
                        
                        {{-- Inject Blank Row when Faculty Changes --}}
                        @if($lastFacultyId !== null && $lastFacultyId !== $block->faculty_id)
                            <tr class="bg-gray-50/50">
                                <td colspan="8" class="py-4"></td> {{-- py-4 provides the "blank" vertical space --}}
                            </tr>
                        @endif

                        <tr class="hover:bg-[#FFF5F5] transition-colors">
                            <td class="px-3 py-0.5 text-slate-400 font-medium whitespace-nowrap">
                                {{ $block->section->program->name ?? 'N/A' }}-{{ $block->section->name ?? 'N/A' }}
                            </td>
                            <td class="px-3 py-0.5 font-bold text-slate-700">{{ $block->course->code }}</td>
                            <td class="px-3 py-0.5 text-slate-500 uppercase truncate max-w-[220px]">{{ $block->course->name }}</td>
                            <td class="px-3 py-0.5 font-semibold text-[#4A5568] whitespace-nowrap">{{ $block->schedule_string }}</td>
                            <td class="px-3 py-0.5 text-[#3182CE] font-bold uppercase">{{ $block->room_name }}</td>
                            <td class="px-3 py-0.5 text-slate-700 font-semibold">
                                {{ $block->faculty->last_name }}, {{ substr($block->faculty->first_name, 0, 1) }}.
                            </td>
                            <td class="px-3 py-0.5 text-slate-400 whitespace-nowrap">
                                {{ $block->semester }} | {{ $block->academicYear->start_year }}-{{ $block->academicYear->end_year }}
                            </td>
                            <td class="px-3 py-0.5 text-right whitespace-nowrap">
                                <div class="flex justify-end gap-3 text-[9px] font-bold">
                                    <a href="{{ route('course_blocks.edit', $block->id) }}" class="text-[#D69E2E] hover:text-[#B7791F]">EDIT</a>
                                    <form action="{{ route('course_blocks.destroy', $block->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[#FEB2B2] hover:text-[#E53E3E]" onclick="return confirm('Delete?')">DEL</button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        @php $lastFacultyId = $block->faculty_id; @endphp

                    @empty
                        <tr><td colspan="8" class="px-6 py-12 text-center text-slate-300 italic">No course blocks found.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-4 py-1.5 bg-[#F7FAFC] border-t border-[#EDF2F7]">
                <div class="scale-90 origin-left">
                    {{ $courseBlocks->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>