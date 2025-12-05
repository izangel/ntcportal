<div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
    <h3 class="text-xl font-semibold text-green-600 mb-4">
        Grading: **{{ $selectedBlock->course->code ?? 'N/A' }}-{{ $selectedBlock->course->name ?? 'N/A' }}** @if ($selectedBlock)
        ({{ $selectedBlock->section->program->name }}-{{ $selectedBlock->section->name }})
        @endif
    </h3>

    <form wire:submit.prevent="saveGrades">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/6">Grade</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($enrolledStudents as $student)
                    <tr wire:key="student-grade-{{ $student['student_id'] }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student['student_id'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $student['student_name'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            
                            {{-- START: REPLACED INPUT WITH SELECT DROPDOWN --}}
                            <select wire:model.defer="grades.{{ $student['student_id'] }}" 
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm p-1 {{ $gradesFinalized ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ $gradesFinalized ? 'disabled' : '' }}>
                                
                                {{-- Default blank option allows for null/empty grade --}}
                                <option value="">-- Select Grade --</option> 

                                {{-- Loop through the options from the computed property --}}
                                @foreach ($this->gradeOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                                
                            </select>
                            {{-- END: REPLACED INPUT WITH SELECT DROPDOWN --}}
                            
                            @error('grades.' . $student['student_id']) 
                                <span class="text-red-500 text-xs">{{ $message }}</span> 
                            @enderror
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500 italic">No students are currently enrolled in this specific course block.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-6 flex justify-between items-center">
            
            {{-- REMOVED: Old, ineffective status-message div is no longer needed 
               as messaging is handled by the parent component via dispatch/session flash --}}
            {{-- <div id="status-message-{{ $blockId }}" class="text-sm font-medium text-gray-600"></div> --}}

            <div class="flex justify-end space-x-4">
                @if (!$gradesFinalized)
                    {{-- This button submits the form and calls saveGrades --}}
                    <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        💾 Save Grades (Draft)
                    </button>
                    
                    {{-- This button opens the modal and calls showSubmitConfirmation --}}
                    <button type="button" 
                            wire:click="showSubmitConfirmation"
                            class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        ✅ Submit Grades (Finalize)
                    </button>
                @else
                    <button type="button" disabled class="py-2 px-4 rounded-md text-sm font-medium text-white bg-gray-500 cursor-not-allowed">
                        Final Grades Submitted
                    </button>
                @endif
            </div>
        </div>
    </form>

    
    @if ($showConfirmationModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-75 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-3/4 md:w-1/2 shadow-lg rounded-md bg-white">
                
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Final Grade Submission</h3>
                <p class="text-red-600 font-semibold mb-6">WARNING: Once submitted, these grades **CANNOT BE EDITED**.</p>

                <div class="max-h-60 overflow-y-auto border p-3 mb-4 rounded-md">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Student Name</th>
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Final Grade</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($enrolledStudents as $student)
                            <tr wire:key="modal-review-{{ $student['student_id'] }}">
                                <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $student['student_name'] }}</td>
                                <td class="px-3 py-2 whitespace-nowrap text-right text-sm font-bold text-gray-700">
                                    {{ $grades[$student['student_id']] ?? 'N/A' }} 
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <div class="flex items-start mb-6">
                    <input id="confirm_grades" type="checkbox" wire:model.live="confirmationChecked" class="mt-1 h-4 w-4 text-red-600 border-gray-300 rounded">
                    <label for="confirm_grades" class="ml-2 text-sm font-medium text-gray-900">
                        I have reviewed all final grades and confirm they are correct and ready for submission.
                    </label>
                </div>

                <div class="flex justify-end space-x-4">
                    <button wire:click="$set('showConfirmationModal', false)" class="py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    
                    <button wire:click="submitFinalGrades"
                            @if (!$confirmationChecked) disabled @endif
                            class="py-2 px-4 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 disabled:bg-red-400">
                        Confirm and Submit Grades
                    </button>

                </div>
            </div>
        </div>
    @endif
</div>