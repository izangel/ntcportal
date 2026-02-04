<div class="bg-white shadow-lg rounded-xl p-6 border border-gray-200">
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between border-b pb-4">
        <div>
            <h3 class="text-xl font-bold text-indigo-700">
                {{ $selectedBlock->course->code }}: {{ $selectedBlock->course->name }}
            </h3>
            <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">
                {{ $selectedBlock->academicYear->start_year }}-{{ $selectedBlock->academicYear->end_year }} | {{ $selectedBlock->semester }} Semester
            </p>
        </div>
        <div class="text-right">
            <span class="block text-sm font-bold text-gray-700">{{ $selectedBlock->schedule_string }}</span>
            <span class="text-xs font-medium px-2 py-0.5 bg-gray-100 rounded text-gray-600">
                Total Students: {{ count($enrolledStudents) }}
            </span>
        </div>
    </div>

    <form wire:submit.prevent="saveGrades">
        <div class="overflow-x-auto rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase w-12">No.</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Student Name</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Program & Section</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-40">Grade</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse ($enrolledStudents as $index => $student)
                    <tr wire:key="row-{{ $student['student_id'] }}" class="hover:bg-gray-50">
                        {{-- Sequential Numbering --}}
                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-400 font-mono">
                            {{ $loop->iteration }}
                        </td>
                        {{-- Name (Sorted by Lastname from your Controller) --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 uppercase">
                            {{ $student['student_name'] }}
                        </td>
                        {{-- Formatted Section: Program-Section --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-600 font-medium">
                                {{ $student['section_name'] }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <select wire:model.defer="grades.{{ $student['student_id'] }}" 
                                    class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 {{ $gradesFinalized ? 'bg-gray-100 cursor-not-allowed' : '' }}"
                                    {{ $gradesFinalized ? 'disabled' : '' }}>
                                <option value="">--</option> 
                                @foreach ($this->gradeOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">No students enrolled.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-8 flex justify-end items-center space-x-4">
            @if (!$gradesFinalized)
                <button type="submit" class="px-6 py-2 bg-white border border-gray-300 rounded-md font-bold text-xs text-gray-700 uppercase hover:bg-gray-50 shadow-sm transition">
                    💾 Save
                </button>
                
                @php
                    // Check if every student in the list has a value in the grades array
                    $totalStudents = count($enrolledStudents);
                    $filledGrades = count(array_filter($grades, fn($value) => $value !== null && $value !== ''));
                    $allFilled = ($totalStudents > 0 && $filledGrades === $totalStudents);
                @endphp

                <button type="button" 
                        wire:click="showSubmitConfirmation"
                        @if(!$allFilled) disabled @endif
                        class="px-6 py-2 bg-red-600 text-white rounded-md font-bold text-xs uppercase tracking-widest hover:bg-red-700 shadow-md transition disabled:bg-gray-300 disabled:cursor-not-allowed"
                        title="{{ !$allFilled ? 'Please assign grades to all students before finalizing' : '' }}">
                    ✅ Submit Grades
                </button>
            @else
                <div class="px-6 py-2 bg-green-50 text-green-700 border border-green-200 rounded-md text-xs font-black uppercase tracking-widest">
                    Records Locked
                </div>
            @endif
        </div>
    </form>


    

    @if($showConfirmationModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border-t-8 border-red-600">
                
                <div class="bg-white px-6 pt-5 pb-4 sm:p-8 sm:pb-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-0 sm:text-left w-full">
                            
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-2xl font-extrabold text-gray-900">Review Final Ratings</h3>
                                <span class="px-3 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full uppercase">
                                    {{ count($enrolledStudents) }} Students Total
                                </span>
                            </div>

                            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 mb-4 grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-2 text-xs">
                                <div>
                                    <label class="block font-semibold text-gray-500 uppercase tracking-wider">Period</label>
                                    <p class="font-bold text-gray-800">{{ $academicPeriod }}</p>
                                </div>
                                <div>
                                    <label class="block font-semibold text-gray-500 uppercase tracking-wider">Schedule</label>
                                    <p class="font-bold text-gray-800">{{ $selectedBlock->schedule_string }}</p>
                                </div>
                                <div class="md:col-span-2 border-t pt-2">
                                    <label class="block font-semibold text-gray-500 uppercase tracking-wider">Course</label>
                                    <p class="font-bold text-gray-900">{{ $selectedBlock->course->code }} — {{ $selectedBlock->course->name }}</p>
                                </div>
                            </div>

                            <div class="mb-6 border border-gray-200 rounded-lg overflow-hidden">
                                <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                                    <span class="text-xs font-bold text-gray-600 uppercase">Student List & Final Ratings</span>
                                </div>
                                <div class="max-h-60 overflow-y-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50 sticky top-0">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Name</th>
                                                <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase">Section</th>
                                                <th class="px-4 py-2 text-center text-[10px] font-bold text-gray-500 uppercase">Rating</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($enrolledStudents as $student)
                                                <tr>
                                                    <td class="px-4 py-2 text-sm text-gray-900 whitespace-nowrap">
                                                        {{ $student['student_name'] }}
                                                    </td>
                                                    <td class="px-4 py-2 text-xs text-gray-500">
                                                        {{ $student['section_name'] }}
                                                    </td>
                                                    <td class="px-4 py-2 text-sm text-center font-bold {{ ($grades[$student['student_id']] ?? '') == '5.0' || ($grades[$student['student_id']] ?? '') == 'DRP' ? 'text-red-600' : 'text-indigo-700' }}">
                                                        {{ $grades[$student['student_id']] ?? 'N/A' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4 rounded-r-lg text-sm">
                                <h4 class="font-bold text-yellow-800 uppercase tracking-tight">Important Notice:</h4>
                                <p class="text-yellow-700 mt-1">
                                    Locking these grades is a permanent action. Please verify that the ratings above match your records.
                                </p>
                            </div>

                            <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-xl">
                                <div class="flex items-start">
                                    <div class="flex items-center h-6">
                                        <input id="confirm_final_lock" type="checkbox" wire:model.live="confirmationChecked" class="h-5 w-5 text-red-600 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
                                    </div>
                                    <div class="ml-4 text-sm">
                                        <label for="confirm_final_lock" class="font-bold text-gray-900 cursor-pointer">Official Certification</label>
                                        <p class="text-gray-600 italic mt-1 leading-snug">
                                            "I hereby certify that the ratings listed for this course and its respective sections are accurate, complete, and finalized based on the academic performance of the students enrolled."
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="bg-gray-100 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-3">
                    <button type="button" wire:click="submitFinalGrades" wire:loading.attr="disabled" @if(!$confirmationChecked) disabled @endif class="w-full inline-flex justify-center rounded-lg shadow-sm px-8 py-2.5 bg-red-600 text-base font-bold text-white hover:bg-red-700 sm:w-auto sm:text-sm transition-all disabled:opacity-50">
                        Confirm and Lock Grades
                    </button>
                    <button type="button" wire:click="$set('showConfirmationModal', false)" class="...">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>