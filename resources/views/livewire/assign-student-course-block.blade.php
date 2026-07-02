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

                    @if($target_section_id)
                        {{-- 1. Active Student Header --}}
                        @if($selected_student_id)
                            @php $activeStudent = App\Models\Student::find($selected_student_id); @endphp
                            <div class="mb-2 p-2 bg-blue-900 text-white rounded shadow flex justify-between items-center transition-all">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] bg-blue-700 px-2 py-0.5 rounded-full font-bold">ACTIVE CONTEXT</span>
                                    <span class="text-xs font-black uppercase tracking-widest">
                                        {{ $activeStudent->last_name }}, {{ $activeStudent->first_name }} 
                                        <span class="text-blue-300 font-normal ml-2">({{ $activeStudent->student_id }})</span>
                                    </span>
                                </div>
                                <button wire:click="$set('selected_student_id', null)" class="text-[10px] bg-red-600 hover:bg-red-700 px-3 py-1 rounded font-bold uppercase transition">
                                    Close Student
                                </button>
                            </div>
                        @endif

                        {{-- 2. Legend / Status Indicators --}}
                        <div class="flex gap-4 mb-2 px-1">
                            <div class="flex items-center gap-1">
                                <span class="text-green-500 text-[10px]">●</span>
                                <span class="text-[8px] font-bold text-gray-500 uppercase">Regular Match</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-purple-600 text-[10px]">★</span>
                                <span class="text-[8px] font-bold text-gray-500 uppercase">Irregular / Back-Subject</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-amber-500 text-[10px]">!</span>
                                <span class="text-[8px] font-bold text-gray-500 uppercase">Missing From Load</span>
                            </div>
                        </div>

                        {{-- 3. Side-by-Side Comparison Grid --}}
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            
                            <div class="bg-white border border-blue-300 rounded shadow-sm overflow-hidden flex flex-col">
                                <div class="bg-blue-600 px-2 py-1.5 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        <span class="text-[10px] font-black text-white uppercase">Official Section Load</span>
                                    </div>
                                    <span class="text-[8px] bg-white text-blue-700 px-1.5 rounded font-black">TEMPLATE</span>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-100">
                                        <thead class="bg-gray-50 text-[8px] uppercase font-bold text-gray-500">
                                            <tr>
                                                <th class="p-1.5 w-6 text-center">Status</th>
                                                <th class="p-1.5 text-left w-20">Code</th>
                                                <th class="p-1.5 text-left">Faculty / Schedule / Room</th>
                                                <th class="p-1.5 w-12">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse($sectionTemplateBlocks as $stb)
                                                @php 
                                                    $isEnrolled = $selected_student_id ? in_array($stb->id, array_column($current_student_load, 'id')) : false; 
                                                @endphp
                                                <tr class="text-[10px] {{ ($selected_student_id && !$isEnrolled) ? 'bg-amber-50' : 'hover:bg-gray-50' }} transition-colors">
                                                    <td class="p-1.5 text-center font-bold">
                                                        @if($selected_student_id)
                                                            {!! $isEnrolled ? '<span class="text-green-500">✓</span>' : '<span class="text-amber-500 animate-pulse">!</span>' !!}
                                                        @else
                                                            <span class="text-gray-300">#</span>
                                                        @endif
                                                    </td>
                                                    <td class="p-1.5 font-bold text-blue-800">{{ $stb->course->code }}</td>
                                                    <td class="p-1.5 leading-tight">
                                                        <div class="font-bold uppercase text-gray-700 truncate w-48">
                                                            {{ $stb->faculty->last_name }}, {{ $stb->faculty->first_name }}
                                                        </div>
                                                        <div class="text-[9px] text-gray-500 flex gap-2">
                                                            <span class="bg-gray-100 px-1 rounded">{{ $stb->schedule_string }}</span>
                                                            <span class="text-blue-600 font-medium">{{ $stb->room_name ?? 'TBA' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="p-1.5 text-right">
                                                        @if($selected_student_id && !$isEnrolled)
                                                            <button wire:click="addSingleBlockToStudent({{ $stb->id }})" 
                                                                class="bg-amber-500 hover:bg-amber-600 text-white px-2 py-0.5 rounded text-[8px] font-black transition shadow-sm">
                                                                FIX
                                                            </button>
                                                        @elseif($selected_student_id && $isEnrolled)
                                                            <span class="text-[8px] font-bold text-green-600 bg-green-50 px-1 rounded">MATCHED</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="4" class="p-6 text-center text-xs text-gray-400 italic">No template defined for this section.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="bg-white border border-green-300 rounded shadow-sm overflow-hidden flex flex-col">
                                <div class="bg-green-600 px-2 py-1.5 flex justify-between items-center">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        <span class="text-[10px] font-black text-white uppercase">Active Student Load</span>
                                    </div>
                                    <span class="text-[8px] bg-white text-green-700 px-1.5 rounded font-black">ENROLLED</span>
                                </div>
                                
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-100">
                                        <thead class="bg-gray-50 text-[8px] uppercase font-bold text-gray-500">
                                            <tr>
                                                <th class="p-1.5 w-6 text-center">Type</th>
                                                <th class="p-1.5 text-left w-20">Code</th>
                                                <th class="p-1.5 text-left">Faculty / Schedule / Room</th>
                                                <th class="p-1.5 w-8"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @forelse($current_student_load as $load)
                                                @php 
                                                    $isFromSection = in_array($load->id, $sectionTemplateBlocks->pluck('id')->toArray()); 
                                                @endphp
                                                <tr class="text-[10px] {{ !$isFromSection ? 'bg-purple-50' : 'hover:bg-gray-50' }} transition-colors">
                                                    <td class="p-1.5 text-center">
                                                        <span class="{{ $isFromSection ? 'text-green-500' : 'text-purple-600 font-bold' }}">
                                                            {{ $isFromSection ? '●' : '★' }}
                                                        </span>
                                                    </td>
                                                    <td class="p-1.5 leading-tight">
                                                        <div class="font-bold {{ $isFromSection ? 'text-green-800' : 'text-purple-800' }}">
                                                            {{ $load->code }}
                                                        </div>
                                                        @if(!$isFromSection)
                                                            <span class="text-[7px] bg-purple-200 text-purple-700 px-1 rounded font-black uppercase tracking-tighter">
                                                                Irregular / Back-Subject
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="p-1.5 leading-tight">
                                                        <div class="font-bold uppercase text-gray-700 truncate w-48">
                                                            {{ $load->last_name }}, {{ $load->first_name }}
                                                        </div>
                                                        <div class="text-[9px] text-gray-500 flex gap-2">
                                                            <span class="bg-gray-100 px-1 rounded">{{ $load->schedule_string ?? '---' }}</span>
                                                            <span class="text-green-600 font-medium">{{ $load->room_name ?? 'TBA' }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="p-1.5 text-center">
                                                        <button wire:click="removeSingleBlock({{ $load->id }})" 
                                                            wire:confirm="Remove this subject from student load?"
                                                            class="text-red-400 hover:text-red-600 transition p-1 rounded hover:bg-red-50">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="p-8 text-center">
                                                        <div class="text-gray-400 italic text-[10px]">
                                                            @if($selected_student_id)
                                                                Student has no subjects enrolled.
                                                            @else
                                                                Select a student to view/edit their active load.
                                                            @endif
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    @endif

                     
                        {{-- DYNAMIC LABEL --}}
                        @if($target_section_id)
                            <span class="flex items-center px-2 py-0.5 rounded text-[9px] font-black uppercase shadow-sm {{ $contextLabel === 'SHS' ? 'bg-amber-500 text-white' : 'bg-blue-600 text-white' }}">
                                <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12" />
                                </svg>
                                Current Catalog: {{ $contextLabel }}
                            </span>
                        @endif

                    <table class="min-w-full table-fixed divide-y divide-gray-200">
                        <thead class="bg-white">
                            <tr class="text-[10px] font-bold text-gray-400 uppercase">
                                <th class="px-2 py-1 w-8"></th>
                                <th class="px-2 py-1 text-left w-1/3">Instructor</th>
                                <th class="px-2 py-1 text-left">Course / Code</th>
                                <th class="px-2 py-1 text-left w-16">Room</th>
                                <th class="px-2 py-1 text-left w-32">Schedule</th>
                                <th class="px-2 py-1 w-20"></th> </tr>
                        </thead>
                       <tbody class="divide-y divide-gray-100" wire:key="tbody-section-{{ $target_section_id }}">
                            @forelse($courseBlocks as $block)
                                <tr wire:key="row-{{ $block->id }}-section-{{ $target_section_id }}" class="{{ $selected_student_id ? 'hover:bg-blue-50' : '' }}">
                                    <td class="px-2 py-0.5 text-center">
                                        <input type="checkbox" 
                                            wire:model.live="selected_course_blocks" 
                                            value="{{ (string)$block->id }}"
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
                                    <td class="px-2 py-0.5 text-right">
                                       @if($selected_student_id)
                                            @php 
                                                $isEnrolled = in_array($block->id, array_column($current_student_load, 'id'));
                                            @endphp

                                            @if($isEnrolled)
                                                <span class="text-[9px] font-bold text-green-600 uppercase">✓ Enrolled</span>
                                            @else
                                                <button wire:click="addSingleBlockToStudent({{ $block->id }})" 
                                                    class="bg-green-600 hover:bg-green-700 text-white px-2 py-0.5 rounded text-[9px] font-bold transition">
                                                    + ENROLL
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="p-4 text-center text-xs text-gray-300 italic">No course blocks available for the selected term.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

           
            <div class="col-span-4 space-y-2">
                 <div class="flex justify-between items-center mb-2">
                    <h3 class="text-[10px] font-black text-gray-700 uppercase">Available Course Blocks</h3>
                    
                    @if($target_section_id)
                        @php $section = \App\Models\Section::find($target_section_id); @endphp
                        <span class="text-[9px] px-2 py-0.5 rounded font-bold {{ $section->program->name === 'SHS' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }}">
                            Filtering for: {{ $section->program->name }}
                        </span>
                    @endif
                </div>
                
                <div class="bg-white p-3 rounded shadow-sm border border-gray-200">
                    <h3 class="text-[10px] font-black text-gray-400 uppercase mb-2">1. Define Section Load</h3>
                    <select wire:model.live="target_section_id" class="w-full text-xs border-gray-300 rounded mb-2 py-1 font-bold text-blue-800">
                        <option value="">-- Choose Target Section --</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->program->name ?? '' }}-{{ $section->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex gap-2">
                        <button wire:click="assignRegularSection" 
                            wire:loading.attr="disabled"
                            @disabled(!$target_section_id)
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded text-xs shadow-sm disabled:opacity-30">
                            <span wire:loading.remove>SAVE & SYNC</span>
                            <span wire:loading>PROCESSING...</span>
                        </button>
                    </div>
                </div>

                <div class="bg-purple-50 p-3 rounded border border-purple-200 mb-2">
                    <h3 class="text-[10px] font-black text-purple-700 uppercase mb-2">Bulk Enrollment (Previous Sem)</h3>
                    
                    <label class="text-[9px] font-bold text-gray-500 uppercase">Pull Students From:</label>
                    <select wire:model.live="source_section_id" class="w-full text-xs border-purple-300 rounded mb-2 py-1">
                        <option value="">-- Select Source Section --</option>
                        @foreach($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->name }} (1st Sem)</option>
                        @endforeach
                    </select>

                    <button wire:click="bulkEnrollFromSection" 
                        wire:confirm="This will enroll ALL students from the selected source section into the current target section template. Continue?"
                        @disabled(!$source_section_id || !$target_section_id)
                        class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 rounded text-[10px] shadow-sm disabled:opacity-30">
                        BATCH ENROLL INTO {{ $semester }}
                    </button>
                </div>

                @if($selected_student_id)
                    @php $activeStudent = App\Models\Student::find($selected_student_id); @endphp
                    <div class="bg-blue-600 p-2 rounded shadow-md text-white flex justify-between items-center animate-pulse">
                        <div class="text-[10px]">
                            <p class="font-black">ENROLLING INDIVIDUAL SUBJECTS FOR:</p>
                            <p class="text-xs uppercase">{{ $activeStudent->last_name }}, {{ $activeStudent->first_name }}</p>
                        </div>
                        <button wire:click="$set('selected_student_id', null)" class="text-white hover:text-red-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
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
                                <button wire:key="late-{{ $fs['id'] }}"
                                    wire:click="assignToSectionAndBlocks({{ $fs['id'] }})" 
                                    class="w-full text-left px-3 py-2 hover:bg-orange-50 border-b last:border-0 text-[11px] flex justify-between items-center">
                                    <div class="flex flex-col">
                                        <span class="font-bold uppercase text-gray-700">{{ $fs['last_name'] }}, {{ $fs['first_name'] }}</span>
                                        <span class="text-[9px] text-gray-400">Click to Enroll to {{ $target_section_id ? 'Section' : 'System' }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

               <div class="bg-white rounded shadow-sm border border-gray-200">
                    <div class="p-2 border-b bg-gray-50 flex justify-between items-center">
                        <h3 class="text-[10px] font-black text-gray-400 uppercase">Section Student List</h3>
                        <span class="text-[9px] text-gray-400 font-bold italic">Click name to add individual subjects</span>
                    </div>
                    <div class="p-2 max-h-[400px] overflow-y-auto">
                        <div class="grid grid-cols-1 gap-1">
                            @forelse($students as $student)
                               <div wire:key="student-item-{{ $student->id }}" 
                                    wire:click="selectStudent({{ $student->id }})"
         
                                    class="cursor-pointer text-[10px] py-1.5 px-2 rounded border transition flex items-center justify-between group 
                                    {{ $selected_student_id == $student->id ? 'bg-blue-50 border-blue-400' : 'bg-white border-transparent hover:bg-gray-50' }}">
                                    
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-400">{{ $loop->iteration }}.</span>
                                        <span class="font-bold uppercase {{ $student->has_load_mismatch ? 'text-amber-600' : 'text-gray-800' }}">
                                            {{ $student->last_name }}, {{ $student->first_name }}
                                        </span>
                                        @if($student->has_load_mismatch)
                                            <span class="text-[8px] bg-amber-100 text-amber-700 px-1 rounded font-black">CUSTOM LOAD</span>
                                        @endif
                                    </div>

                                    @if($selected_student_id == $student->id)
                                        <span class="text-[8px] font-black text-blue-600 animate-pulse">EDITING LOAD</span>
                                    @endif
                                </div>
                            @empty
                                <div class="text-[10px] text-gray-400 italic text-center py-4">No students in section.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                @if($selected_student_id && !empty($current_student_load))
                <div class="bg-white border-2 border-blue-400 rounded p-2 mb-2 shadow-sm">
                    <h4 class="text-[10px] font-black text-blue-800 uppercase mb-1">Confirmed Enrollment Load:</h4>
                    <div class="space-y-1">
                        @foreach($current_student_load as $load)
                            <div class="flex justify-between items-center bg-blue-50 p-1 rounded text-[9px] border border-blue-100">
                                <span class="font-bold text-blue-900">{{ $load->code }}</span>
                                <span class="text-gray-500 truncate ml-2">{{ $load->last_name }}</span>
                                
                                {{-- Ensure this matches $load->id --}}
                                <button wire:click="removeSingleBlock({{ $load->id }})" class="text-red-500 ml-1">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
@endif

            </div>
        </div>

        @if (session()->has('message'))
            <div class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-2xl text-xs font-bold animate-bounce">
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="fixed bottom-4 right-4 bg-red-600 text-white px-4 py-2 rounded shadow-2xl text-xs font-bold">
                {{ session('error') }}
            </div>
        @endif
    </div>
</div>