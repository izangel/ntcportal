<div class="p-2 bg-gray-100 min-h-screen">
    <div class="mx-auto max-w-[1600px]">
        
        <div class="flex justify-between items-center mb-2 bg-white p-2 rounded shadow-sm border border-gray-200">
            <div class="flex items-center gap-4">
                <h1 class="text-base font-black text-gray-800 uppercase tracking-tighter">Section Load Manager</h1>
                <div class="flex gap-2 border-l pl-4 border-gray-200">
                    <button wire:click="printClassList" 
                        @disabled(!$target_section_id)
                        class="flex items-center gap-1 bg-gray-800 text-white px-3 py-1 rounded text-[10px] font-bold hover:bg-black disabled:opacity-30">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        PRINT CLASS LIST
                    </button>
                </div>
            </div>
            
            <div class="w-1/3">
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Search Code, Subject, or Instructor..." 
                    class="w-full px-2 py-1 border-gray-300 rounded text-xs shadow-sm focus:ring-1 focus:ring-blue-500"
                    @disabled(!$academic_year_id || !$semester)>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-2">
            
            <div class="col-span-8">
                <div class="bg-white rounded shadow-sm border border-gray-200">
                    <div class="grid grid-cols-2 gap-2 p-1.5 bg-gray-50 border-b">
                        <select wire:model.live="academic_year_id" class="w-full text-[11px] border-gray-300 rounded py-0.5">
                            <option value="">Select Academic Year</option>
                            @foreach($academicYears as $ay)
                                <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                            @endforeach
                        </select>
                        <select wire:model.live="semester" class="w-full text-[11px] border-gray-300 rounded py-0.5">
                            <option value="">Select Semester</option>
                            @foreach($semesters as $sem)
                                <option value="{{ $sem }}">{{ $sem }}</option>
                            @endforeach
                        </select>
                    </div>

                    <table class="min-w-full table-fixed divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr class="text-[10px] font-bold text-gray-400 uppercase">
                                <th class="px-2 py-1 w-8"></th>
                                <th class="px-2 py-1 text-left w-1/3">Instructor (Full Name)</th>
                                <th class="px-2 py-1 text-left">Course / Code</th>
                                <th class="px-2 py-1 text-left w-16">Room</th>
                                <th class="px-2 py-1 text-left w-32">Schedule</th>
                            </tr>
                        </thead>
                       <tbody class="divide-y divide-gray-100" wire:key="tbody-section-{{ $target_section_id }}">
                            @forelse($courseBlocks as $block)
                                <tr wire:key="row-{{ $block->id }}-section-{{ $target_section_id }}">
                                    <td class="px-2 py-0.5 text-center">
                                        <input type="checkbox" 
                                            wire:model.live="selected_course_blocks" 
                                            value="{{ (string)$block->id }}" {{-- Cast the value to string here too --}}
                                            wire:key="cb-{{ $block->id }}-{{ $target_section_id }}"
                                            class="w-3 h-3 rounded text-blue-600">
                                    </td>
                                   
                                    <td class="px-2 py-0.5 text-[11px] truncate">
                                        <span class="font-bold text-gray-900 uppercase">{{ $block->faculty->last_name }}</span>, 
                                        <span class="text-gray-600 capitalize">{{ $block->faculty->first_name }}</span>
                                    </td>
                                    <td class="px-2 py-0.5 text-[11px]">
                                        <span class="font-bold text-blue-700">{{ $block->course->code }}</span>
                                        <span class="text-gray-400 ml-1">- {{ $block->course->name }}</span>
                                    </td>
                                    <td class="px-2 py-0.5 text-[11px] font-medium text-gray-500 uppercase">{{ $block->room_name ?? 'TBA' }}</td>
                                    <td class="px-2 py-0.5 text-[10px] text-gray-400 tabular-nums">
                                        {{ $block->schedule_string ?? '---' }}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="p-4 text-center text-xs text-gray-300 italic">No course blocks available for the selected term.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-span-4 space-y-2">
                
                <div class="bg-white p-3 rounded shadow-sm border border-gray-200">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase mb-2">1. Define Section Load</h3>
                        <select wire:model.live="target_section_id" class="w-full text-xs border-gray-300 rounded mb-2 py-1 font-bold text-blue-800">
                            <option value="">-- Choose Target Section --</option>
                            @foreach($sections as $section)
                            <option value="{{ $section->id }}">
                            {{ $section->program->name ?? 'N/A' }} {{ $section->name }}
                            </option>
                             @endforeach
                        </select>

                    <div class="flex gap-2">
                        <button wire:click="assignRegularSection" 
                            wire:loading.attr="disabled"
                            @disabled(!$target_section_id) {{-- Removed the count check here --}}
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded text-xs shadow-sm disabled:opacity-30">
                            <span wire:loading.remove>SAVE & SYNC</span>
                            <span wire:loading>PROCESSING...</span>


                        </button>

                        <button wire:click="globalSyncCleanup" 
                            wire:confirm="This will remove all subjects from students in this section that don't match the current template. Continue?"
                            class="text-[9px] bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 px-2 py-1 rounded transition uppercase font-bold">
                            Fix Duplicate Loads
                        </button>

                        <button wire:click="resetSectionLoad" 
                            wire:loading.attr="disabled"
                            wire:confirm="WARNING: This will remove ALL course blocks from the section template AND from all students currently in this section. Proceed?"
                            @disabled(!$target_section_id)
                            class="px-3 bg-white border border-red-200 text-red-500 hover:bg-red-50 rounded text-xs transition"
                            title="Reset Section Load">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                </div>

                @if($target_section_id && count($sectionTemplateBlocks) > 0)
                    <div class="bg-blue-50 rounded border border-blue-100 overflow-hidden">
                        <div class="p-2 border-b border-blue-100 bg-blue-100/50 flex justify-between items-center">
                            <h3 class="text-[10px] font-black text-blue-800 uppercase tracking-tighter">Current Section Load</h3>
                            <span class="text-[9px] font-bold text-blue-600 uppercase">Saved Blocks</span>
                        </div>
                        <table class="min-w-full text-[10px]">
                            <tbody class="divide-y divide-blue-100">
                                @foreach($sectionTemplateBlocks as $stb)
                                <tr class="bg-white/50">
                                    <td class="px-2 py-1 font-bold text-blue-900 w-16">{{ $stb->course->code }}</td>
                                    <td class="px-2 py-1 text-gray-600 truncate">
                                        {{ $stb->faculty->last_name }}, {{ substr($stb->faculty->first_name, 0, 1) }}.
                                    </td>
                                    <td class="px-2 py-1 text-right text-gray-400 font-mono">{{ $stb->room }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="bg-orange-50 p-3 rounded border border-orange-200 relative">
                    <h3 class="text-[10px] font-black text-orange-700 uppercase mb-2">2. Late Enrollee Search</h3>
                    <input type="text" wire:model.live="student_search" 
                        placeholder="Type student name..." 
                        class="w-full text-xs border-orange-300 rounded py-1 focus:ring-orange-500"
                        @disabled(!$target_section_id)>

                    @if(!empty($found_students))
                        <div class="absolute z-50 left-0 right-0 mx-2 bg-white border border-gray-300 shadow-2xl rounded mt-1 overflow-hidden">
                            @foreach($found_students as $fs)
                                @php
                                    $currentSection = DB::table('section_student')
                                        ->join('sections', 'section_student.section_id', '=', 'sections.id')
                                        ->where('section_student.student_id', $fs['id'])
                                        // Prefix these three to be safe
                                        ->where('section_student.academic_year_id', $academic_year_id)
                                        ->where('section_student.semester', $semester)
                                        ->value('sections.name'); // Also explicitly select sections.name
                                @endphp
                                <button wire:key="late-{{ $fs['id'] }}"
                                    wire:mousedown="assignToSectionAndBlocks({{ $fs['id'] }})" 
                                    class="w-full text-left px-3 py-2 hover:bg-orange-50 border-b last:border-0 text-[11px] flex justify-between items-center group">
                                    <div class="flex flex-col">
                                        <span class="font-bold uppercase text-gray-700">{{ $fs['last_name'] }}, {{ $fs['first_name'] }}</span>
                                        @if($currentSection)
                                            <span class="text-[9px] text-red-500 font-bold uppercase">Currently in: {{ $currentSection }} (Click to Transfer)</span>
                                        @else
                                            <span class="text-[9px] text-gray-400 uppercase">Unassigned (Click to Enroll)</span>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

               <div class="bg-white rounded shadow-sm border border-gray-200">
                    <div class="p-2 border-b bg-gray-50 flex justify-between items-center">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase">Section Student List</h3>
                        <span class="bg-blue-600 text-white text-[9px] px-1.5 py-0.5 rounded-full font-bold">{{ count($students) }}</span>
                    </div>
                    <div class="p-2">
                        <div class="grid grid-cols-1 gap-0.5">
                            @forelse($students as $student)
                                <div class="text-[10px] py-1 px-1 hover:bg-gray-50 rounded flex items-center justify-between group">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400">{{ $loop->iteration }}.</span>
                                        <span class="font-bold uppercase {{ $student->has_load_mismatch ? 'text-red-600' : 'text-gray-800' }}">
                                            {{ $student->last_name }}, {{ $student->first_name }}
                                        </span>

                                        @if($student->has_load_mismatch)
                                            <span class="bg-red-100 text-red-700 text-[8px] px-1 rounded-sm font-black animate-pulse">
                                                LOAD OVERFLOW: {{ $student->actual_block_count }} BLOCKS
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2">
                                        @if($student->has_load_mismatch)
                                            <button wire:click="fixSingleStudentLoad({{ $student->id }})" 
                                                    title="Sync Load to Template"
                                                    class="text-amber-500 hover:text-amber-700">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                            </button>
                                        @endif
                                        
                                        <button wire:click="removeStudent({{ $student->id }})" class="...">
                                            </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-[10px] text-gray-400 italic text-center py-4">No students officially in section.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (session()->has('message'))
            <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-2xl text-xs font-bold animate-fade-in-up">
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>