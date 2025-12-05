<div class="bg-yellow-50 border-l-4 border-yellow-500 text-yellow-800 p-4 shadow-md rounded-md mt-4">
    <div class="font-bold text-lg mb-2">Incomplete (INC) Grade Resolution</div>
    
    @if (empty($incStudents))
        <div class="text-center py-8 text-gray-500 italic">
            All incomplete (INC) grades for this block have been resolved.
        </div>
    @else
        <p class="mb-4">Select a student below to resolve their "INC" grade.</p>
        
        <div class="bg-white p-4 rounded-lg shadow mb-6">
            <label for="student-select" class="block text-sm font-medium text-gray-700 mb-2">
                Student with INC Grade ({{ count($studentList) }} pending)
            </label>
            {{-- Dropdown for selecting the student. wire:model.live is used to immediately update the resolution panel below. --}}
            <select id="student-select" 
                    wire:model.live="selectedStudentId" 
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                <option value="" disabled>-- Select Student --</option>
                {{-- $studentList contains ID => Name pairs --}}
                @foreach ($studentList as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            
            {{-- Display an error if the user hasn't selected a student but tries to submit --}}
            @error('selectedStudentId')
                <span class="text-red-500 text-xs mt-1 block">Please select a student to resolve their grade.</span>
            @enderror
        </div>

        {{-- Resolution Panel: Only displayed if a valid student is selected --}}
        @if ($selectedStudentId && isset($incStudents[$selectedStudentId]))
            @php
                $student = $incStudents[$selectedStudentId];
            @endphp
            
            <div class="border border-yellow-300 bg-white p-6 rounded-lg shadow-inner">
                <h4 class="text-xl font-semibold mb-4 text-gray-900">
                    Resolving: **{{ $student['student_name'] }}**
                </h4>
                
                {{-- Using a dedicated form submission is cleaner for validation tracking --}}
                <form wire:submit.prevent="resolveGrade">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        
                        {{-- Current Grade Display --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Current Grade</label>
                            <div class="mt-1 text-2xl font-bold text-red-600">
                                {{ $student['current_grade'] }}
                            </div>
                        </div>
                        
                        {{-- New Grade Dropdown --}}
                        <div>
                            <label for="new-grade" class="block text-sm font-medium text-gray-700">Select Final Grade</label>
                            <select id="new-grade"
                                    {{-- Use $selectedStudentId for deferred model binding --}}
                                    wire:model.defer="resolvedGrades.{{ $selectedStudentId }}"
                                    class="w-full rounded-md border-gray-300 shadow-sm text-lg p-2 mt-1">
                                <option value="">-- Select Final Grade --</option> 
                                @foreach ($this->numericalGradeOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            {{-- Error message specific to this student's grade input --}}
                            @error('resolvedGrades.' . $selectedStudentId) 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>
                        
                        {{-- Resolve Button --}}
                        <div>
                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-base disabled:bg-gray-400">
                                Resolve Grade
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        @else
            <div class="text-center py-4 text-gray-500 italic">
                Select a student from the dropdown above to continue.
            </div>
        @endif
    @endif
</div>