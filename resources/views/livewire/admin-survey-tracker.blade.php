<div class="p-6 bg-white rounded-xl shadow-lg">
    <div class="flex gap-4 mb-6">
        <input type="text" wire:model.live="search" placeholder="Search Student ID or Name..." class="flex-1 rounded-lg border-gray-300">
        </div>

    @if($student)
        <div class="mb-4 p-4 bg-indigo-50 rounded-lg border border-indigo-100">
            <p class="font-bold text-indigo-900">{{ $student->last_name }}, {{ $student->first_name }}</p>
            <p class="text-xs text-indigo-600">{{ $student->student_id_number }}</p>
        </div>

        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="p-3 text-left">Subject</th>
                    <th class="p-3 text-center">Survey Status</th>
                    <th class="p-3 text-center">Rating Given</th>
                </tr>
            </thead>
            <tbody>
                @foreach($enrollments as $enrollment)
                @php
                    $survey = \App\Models\CourseSurvey::where('student_id', $student->id)
                        ->where('course_id', $enrollment->course_id)
                        ->where('academic_year_id', $this->selectedAY)
                        ->first();
                @endphp
                <tr class="border-b">
                    <td class="p-3">
                        <span class="font-bold">{{ $enrollment->course->code }}</span><br>
                        <span class="text-gray-500 text-xs">{{ $enrollment->course->name }}</span>
                    </td>
                    <td class="p-3 text-center">
                        @if($survey)
                            <span class="text-green-600 font-bold font-sans">✓ COMPLETED</span>
                        @else
                            <span class="text-red-500 font-bold font-sans">⚠ NOT ANSWERED</span>
                        @endif
                    </td>
                    <td class="p-3 text-center">
                        {{ $survey ? $survey->rating . ' / 5' : '--' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-12 text-gray-400">
            Enter a student name or ID to view survey records.
        </div>
    @endif
</div>