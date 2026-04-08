<div class="p-6">
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">{{ session('message') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 bg-white p-4 rounded shadow">
        <select wire:model.live="academic_year_id" class="rounded border-gray-300">
            <option value="">Select Year</option>
            @foreach($academicYears as $ay)
                <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
            @endforeach
        </select>

        <select wire:model.live="semester" class="rounded border-gray-300">
            <option value="">Select Semester</option>
            @foreach($semesters as $sem)
                <option value="{{ $sem }}">{{ $sem }}</option>
            @endforeach
        </select>

        <select wire:model.live="section_id" class="rounded border-gray-300">
            <option value="">Select Section</option>
            @foreach($sections as $sec)
                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 uppercase text-xs font-bold">
                <tr>
                    <th class="px-6 py-3 text-left">Last Name</th>
                    <th class="px-6 py-3 text-left">First Name</th>
                    <th class="px-6 py-3 text-left">Middle Name</th>
                    <th class="px-6 py-3 text-left">Student ID</th>
                    <th class="px-6 py-3 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($assigned as $item)
                    <tr wire:key="student-assign-{{ $item->id }}"> <td class="px-6 py-4">{{ $item->student->last_name }}</td>
                    <td class="px-6 py-4">{{ $item->student->first_name }}</td>
                    <td class="px-6 py-4">{{ $item->student->middle_name }}</td>
                    <td class="px-6 py-4 font-mono">{{ $item->student->student_id }}</td>
                    <td class="px-6 py-4 text-right">
                <button wire:click="confirmDelete({{ $item->id }})" class="text-red-600 font-bold hover:underline">
                Remove
                </button>
                </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-10 text-center text-gray-400 italic">Select all filters to view students.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($section_id && $semester && $academic_year_id)
        <div class="p-6 bg-gray-50 border-t flex items-center gap-4">
            <button wire:click="$set('isAdding', true)" class="bg-indigo-600 text-white px-4 py-2 rounded font-bold">
                + Add student to Section
            </button>

            <select wire:model="selected_student_id" @disabled(!$isAdding) class="rounded border-gray-300 disabled:bg-gray-200">
                <option value="">Select Student...</option>
                @foreach($allStudents as $student)
                    <option value="{{ $student->id }}">{{ $student->last_name }}, {{ $student->first_name }}</option>
                @endforeach
            </select>

            <button wire:click="addStudent" @disabled(!$isAdding) class="bg-green-600 text-white px-6 py-2 rounded font-bold disabled:opacity-50">
                Add
            </button>
        </div>
        @endif
    </div>

    @if($confirmingDeletion)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-xl">
            <h2 class="text-lg font-bold mb-4">Confirm Removal</h2>
            <p class="text-gray-600 mb-6">Are you sure you want to remove this student from the section?</p>
            <div class="flex justify-end gap-3">
                <button wire:click="$set('confirmingDeletion', false)" class="px-4 py-2 bg-gray-200 rounded">Cancel</button>
                <button wire:click="deleteAssignment" class="px-4 py-2 bg-red-600 text-white rounded">Yes, Remove</button>
            </div>
        </div>
    </div>
    @endif
</div>