{{-- resources/views/reports/student_types.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('New vs. Old Students Report') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">New vs. Old Student Count Per Semester</h3>

                {{-- Filter Form --}}
                <div class="mb-6">
                    <form method="GET" action="{{ route('reports.studentTypes') }}" class="flex flex-wrap items-center gap-4">
                        <div>
                            <x-label for="academic_year_id" value="{{ __('Academic Year') }}" class="sr-only"/>
                            <select id="academic_year_id_type" name="academic_year_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Academic Years</option>
                                @foreach ($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $selectedAcademicYearId == $year->id ? 'selected' : '' }}>
                                        {{ $year->start_year }} - {{ $year->end_year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <x-label for="semester_id" value="{{ __('Semester') }}" class="sr-only"/>
                            <select id="semester_id_type" name="semester_id" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Semesters</option>
                                @foreach ($semestersList as $semester)
                                    <option value="{{ $semester->id }}" {{ $selectedSemesterId == $semester->id ? 'selected' : '' }}>
                                        {{ $semester->academicYear->start_year ?? '' }} - {{ $semester->academicYear->end_year ?? '' }} {{ $semester->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <x-button type="submit">
                            {{ __('Generate Report') }}
                        </x-button>
                        @if ($selectedAcademicYearId || $selectedSemesterId)
                            <a href="{{ route('reports.studentTypes') }}" class="text-sm text-gray-600 hover:text-gray-900">Clear Filters</a>
                        @endif
                    </form>
                </div>

                @if ($selectedSemesterId && $reportData->isEmpty())
                    <p class="text-gray-600">No enrollments found for the selected semester.</p>
                @elseif ($reportData->isEmpty() && !$selectedSemesterId)
                    <p class="text-gray-600">Please select a semester and click "Generate Report" to see the summary.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Academic Year</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Students</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Old Students</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Unique Students</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($reportData as $row)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $row->start_year }} - {{ $row->end_year }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $row->semester_name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $row->new_student_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $row->old_student_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $row->total_unique_students_enrolled }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- JavaScript for dynamic semester loading --}}
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var academicYearSelect = document.getElementById('academic_year_id_type'); // Use unique ID
            var semesterSelect = document.getElementById('semester_id_type'); // Use unique ID

            function loadSemesters(academicYearId, selectedSemesterId = null) {
                semesterSelect.innerHTML = '<option value="">All Semesters</option>'; // Reset semesters

                if (academicYearId) {
                    fetch(`{{ route('api.semestersByAcademicYear') }}?academic_year_id=${academicYearId}`)
                        .then(response => response.json())
                        .then(data => {
                            data.forEach(semester => {
                                let option = document.createElement('option');
                                option.value = semester.id;
                                option.textContent = semester.name;
                                if (selectedSemesterId && semester.id == selectedSemesterId) {
                                    option.selected = true;
                                }
                                semesterSelect.appendChild(option);
                            });
                        });
                }
            }

            // Initial load if an academic year is already selected (e.g., after filter submission)
            if (academicYearSelect.value) {
                loadSemesters(academicYearSelect.value, "{{ $selectedSemesterId }}");
            }

            // Event listener for academic year change
            academicYearSelect.addEventListener('change', function() {
                loadSemesters(this.value);
            });
        });
    </script>
    @endpush
@endsection