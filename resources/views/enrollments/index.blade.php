@extends('admin.admin')

@section('content')
<div class="bg-gray-50 min-h-screen antialiased">
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-xl font-bold text-gray-900">Student Enrollment Management</h1>
            <p class="text-sm text-gray-500 mt-1">Register students to sections and courses for the academic term.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-12 gap-8">
            
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden sticky top-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">New Enrollment</h2>
                    </div>
                    
                    <form action="{{ route('enrollments.store') }}" method="POST" class="p-6 space-y-5">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Student</label>
                            <select name="student_id" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500">
                                <option value="">Select Student</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->last_name }}, {{ $student->first_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Course</label>
                            <select name="course_id" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500">
                                <option value="">Select Course</option>
                                @foreach($courses as $course)
                                    <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                        {{ $course->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Section</label>
                            <select name="section_id" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500">
                                <option value="">Select Section</option>
                                @foreach($sections as $section)
                                    <option value="{{ $section->id }}" {{ old('section_id') == $section->id ? 'selected' : '' }}>
                                        {{ $section->program->name }} : {{ $section->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Year</label>
                                <select name="academic_year_id" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm">
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Semester</label>
                                <select name="semester" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm">
                                    <option value="1st">1st Sem</option>
                                    <option value="2nd">2nd Sem</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 transition duration-150">
                            Enroll Student
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-8">
                <div class="mb-6">
                    <form action="{{ route('enrollments.index') }}" method="GET" id="filterForm" class="flex flex-col md:flex-row gap-3 items-end">
                        <div class="flex-1 w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2 ml-1">Filter by Student</label>
                            <div class="relative">
                                <select name="student_id" 
                                        onchange="document.getElementById('filterForm').submit()"
                                        class="block w-full pl-3 pr-10 py-2 border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 appearance-none bg-white">
                                    <option value="">Show All Students</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                                            {{ $student->last_name }}, {{ $student->first_name }} ({{ $student->id }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                                </div>
                            </div>
                        </div>

                        @if(request('student_id'))
                            <a href="{{ route('enrollments.index') }}" 
                            class="px-4 py-2 bg-gray-100 text-gray-600 text-sm font-bold rounded-lg hover:bg-gray-200 transition border border-gray-200 flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white">
                        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Active Enrollments</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                            {{ $assignments->total() }} Records Found
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Course / Section</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Term</th>
                                    <th class="relative px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($assignments as $enroll)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-bold text-gray-900">{{ $enroll->student->last_name }}, {{ $enroll->student->first_name }}</div>
                                        <div class="text-xs text-gray-500">ID: {{ $enroll->student_id }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-medium text-gray-800">{{ $enroll->course->name }}</div>
                                        <div class="text-xs text-blue-600 font-semibold uppercase tracking-tight">
                                            {{ $enroll->section->program->name }} : {{ $enroll->section->name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $enroll->semester }} | {{ $enroll->academicYear->start_year }}-{{ $enroll->academicYear->end_year }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('enrollments.destroy', $enroll) }}" method="POST" onsubmit="return confirm('Remove student from this course?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500 text-sm">No enrollment records found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($assignments->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 bg-white">
                            {{ $assignments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection