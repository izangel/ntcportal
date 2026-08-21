@extends('layouts.admin')

@section('title', 'Syllabus Submission Status')

@section('content')
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Syllabus Submission Status</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $activeSemesterLabel }}</p>
        </div>
    </div>

    @if (empty($rows))
        <div class="bg-white rounded-lg shadow p-10 text-center text-gray-400">
            No course blocks found for the active semester.
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            @foreach ([
                'none' => ['Not Prepared', 'bg-gray-100 text-gray-600'],
                'draft' => ['Drafts', 'bg-yellow-50 text-yellow-700 border border-yellow-200'],
                'submitted' => ['Submitted', 'bg-indigo-50 text-indigo-700 border border-indigo-200'],
                'reviewed' => ['Reviewed', 'bg-blue-50 text-blue-700 border border-blue-200'],
                'approved' => ['Approved', 'bg-emerald-50 text-emerald-700 border border-emerald-200'],
                'revision' => ['In Revision', 'bg-amber-50 text-amber-700 border border-amber-200'],
            ] as $key => [$label, $classes])
                <div class="bg-white rounded-lg shadow p-4 text-center">
                    <div class="text-3xl font-black text-gray-800">{{ $stats[$key] ?? 0 }}</div>
                    <div class="text-xs font-semibold text-gray-500 mt-1">{{ $label }}</div>
                </div>
            @endforeach
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Faculty</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sections</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Schedule</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Syllabus Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($rows as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800 text-sm">{{ $row['course_code'] }}</div>
                                <div class="text-xs text-gray-500">{{ $row['course_name'] }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['faculty'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $row['sections'] }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $row['schedule'] }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @forelse ($row['programs'] as $program)
                                        @php
                                            $color = match ($program['status']['key']) {
                                                'approved' => 'bg-emerald-100 text-emerald-800',
                                                'reviewed' => 'bg-blue-100 text-blue-800',
                                                'submitted' => 'bg-indigo-100 text-indigo-800',
                                                'draft' => 'bg-yellow-100 text-yellow-800',
                                                'revision' => 'bg-amber-100 text-amber-800',
                                                default => 'bg-gray-100 text-gray-600',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}" title="{{ $program['name'] }}">
                                            {{ $program['status']['label'] }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400 italic">No sections assigned</span>
                                    @endforelse
                                </div>
                                @if ($row['programs']->isNotEmpty())
                                    <div class="mt-1 text-[10px] text-gray-400">
                                        @foreach ($row['programs'] as $program)
                                            <span class="mr-2">{{ $program['name'] }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection