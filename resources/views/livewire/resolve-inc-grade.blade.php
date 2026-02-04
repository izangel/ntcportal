<div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 shadow-md rounded-md mt-4">
    <div class="font-bold text-lg mb-2">Incomplete (INC) Grade Resolution</div>
    
    {{-- CRITICAL FIX: Check count($incStudents) as the single source of truth --}}
    @if (empty($incStudents))
        <div class="bg-white border border-yellow-200 rounded-lg py-10 text-center shadow-inner">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-gray-600 font-bold text-lg">Fully Resolved</div>
            <p class="text-gray-500 italic text-sm">All incomplete (INC) grades for this block have been resolved.</p>
        </div>
    @else
        <p class="mb-4 text-sm">A total of <strong>{{ count($incStudents) }}</strong> records remain incomplete. Select a student to provide a numerical rating.</p>
        
        <div class="bg-white p-4 rounded-lg shadow mb-6 border border-yellow-200">
            <label for="student-select" class="block text-sm font-bold text-gray-700 mb-2">
                Pending Students
            </label>
            <select id="student-select" 
                    wire:model.live="selectedStudentId" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 text-sm">
                <option value="">-- Select Student to Resolve --</option>
                @foreach ($studentList as $id => $displayName)
                    <option value="{{ $id }}">{{ $displayName }}</option>
                @endforeach
            </select>
            
            @error('selectedStudentId')
                <span class="text-red-500 text-xs mt-1 block">Please select a student.</span>
            @enderror
        </div>

        @if ($selectedStudentId && isset($incStudents[$selectedStudentId]))
            @php $student = $incStudents[$selectedStudentId]; @endphp
            
            <div class="border-2 border-yellow-400 bg-white p-6 rounded-lg shadow-lg">
                <div class="flex justify-between items-start mb-4">
                    <h4 class="text-xl font-bold text-gray-900">
                        {{ $student['student_name'] }}
                    </h4>
                    <span class="px-2 py-1 bg-indigo-100 text-indigo-700 text-xs font-bold rounded">
                        {{ $student['section_name'] }}
                    </span>
                </div>
                
                <form wire:submit.prevent="resolveGrade">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase">Current Status</label>
                            <div class="mt-1 text-2xl font-black text-red-600 tracking-tighter">
                                {{ $student['current_grade'] }}
                            </div>
                        </div>
                        
                        <div>
                            <label for="new-grade" class="block text-xs font-bold text-gray-500 uppercase mb-1">Final Rating</label>
                            <select id="new-grade"
                                    wire:model.defer="resolvedGrades.{{ $selectedStudentId }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-lg font-bold">
                                <option value="">-- Rating --</option> 
                                @foreach ($this->numericalGradeOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('resolvedGrades.' . $selectedStudentId) 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>
                        
                        <div>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded shadow-md transition-all active:scale-95 disabled:bg-gray-400">
                                <span wire:loading.remove>Update Record</span>
                                <span wire:loading>Processing...</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="text-center py-6 bg-yellow-100/50 border border-dashed border-yellow-300 rounded-lg text-yellow-700 text-sm">
                Select a student from the list above to open the resolution tools.
            </div>
        @endif
    @endif
</div>