@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-3xl text-gray-900 leading-tight tracking-tight">
                {{ __('Classify Graduating Students') }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400 font-medium">| Manage graduating student classification</span>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="py-10 bg-gray-50/50 min-h-screen" x-data="{ showModal: false, selectedStudentId: null, selectedStudentName: null, classificationType: null }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        @if(session('status'))
            <div class="rounded-3xl bg-emerald-50 border border-emerald-200 p-6 text-emerald-900">
                {{ session('status') }}
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <h3 class="text-xl font-semibold text-gray-900">All students</h3>
                    <p class="text-sm text-gray-500">Search, sort, and classify graduating students.</p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <form method="GET" action="{{ route('employee.clearance.classify') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <label class="sr-only" for="search">Search</label>
                        <input id="search" name="search" value="{{ request('search') }}" type="search" placeholder="Search by name" class="w-full min-w-[220px] rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100" />

                        <label class="sr-only" for="classification_filter">Classification</label>
                        <select id="classification_filter" name="classification_filter" class="rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="" {{ request('classification_filter') === '' ? 'selected' : '' }}>All Classifications</option>
                            <option value="shs" {{ request('classification_filter') === 'shs' ? 'selected' : '' }}>SHS Graduating</option>
                            <option value="college" {{ request('classification_filter') === 'college' ? 'selected' : '' }}>College Graduating</option>
                            <option value="unclassified" {{ request('classification_filter') === 'unclassified' ? 'selected' : '' }}>Unclassified</option>
                        </select>

                        <label class="sr-only" for="sort_by">Sort by</label>
                        <select id="sort_by" name="sort_by" class="rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="last_name" {{ request('sort_by') === 'last_name' ? 'selected' : '' }}>Name</option>
                            <option value="student_id" {{ request('sort_by') === 'student_id' ? 'selected' : '' }}>Student ID</option>
                        </select>

                        <label class="sr-only" for="sort_direction">Sort direction</label>
                        <select id="sort_direction" name="sort_direction" class="rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100">
                            <option value="asc" {{ request('sort_direction') === 'desc' ? '' : 'selected' }}>Ascending</option>
                            <option value="desc" {{ request('sort_direction') === 'desc' ? 'selected' : '' }}>Descending</option>
                        </select>

                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Apply</button>
                    </form>

                    <a href="{{ route('employee.clearance.classify') }}" class="inline-flex items-center justify-center rounded-2xl border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Remove filters</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Student ID</th>
                            <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Name</th>
                            <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Email</th>
                            <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Classification</th>
                            <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        @forelse($students as $student)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900 font-medium">{{ $student->student_id ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($student->clearanceShs)
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">SHS Graduating</span>
                                    @elseif($student->clearanceCollege)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">College Graduating</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-700">Unclassified</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <button type="button" @click="selectedStudentId = {{ $student->id }}; selectedStudentName = '{{ addslashes($student->last_name . ', ' . $student->first_name) }}'; showModal = true" class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Classify</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6" x-data="{ open: false }">
                <button @click="open = !open" class="flex w-full items-center justify-between rounded-3xl bg-gray-50 px-6 py-4 text-left text-sm font-semibold text-gray-900 hover:bg-gray-100 focus:outline-none">
                    <span>Senior High School Graduating</span>
                    <i class="fas fa-chevron-down transition" :class="{'rotate-180': open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms class="mt-6">
                    @if($classifiedShs->isEmpty())
                        <p class="text-sm text-gray-500">No SHS graduating students classified yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Student ID</th>
                                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Name</th>
                                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($classifiedShs as $student)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $student->student_id ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->email }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-6" x-data="{ open: false }">
                <button @click="open = !open" class="flex w-full items-center justify-between rounded-3xl bg-gray-50 px-6 py-4 text-left text-sm font-semibold text-gray-900 hover:bg-gray-100 focus:outline-none">
                    <span>College Graduating</span>
                    <i class="fas fa-chevron-down transition" :class="{'rotate-180': open}"></i>
                </button>
                <div x-show="open" x-collapse.duration.300ms class="mt-6">
                    @if($classifiedCollege->isEmpty())
                        <p class="text-sm text-gray-500">No College graduating students classified yet.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Student ID</th>
                                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Name</th>
                                        <th class="px-6 py-3 text-left font-semibold uppercase tracking-wide text-gray-700">Email</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($classifiedCollege as $student)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $student->student_id ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-gray-700">{{ $student->email }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4 py-6">
            <div class="w-full max-w-lg rounded-3xl bg-white p-6 shadow-xl">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">Classify graduating student</h3>
                        <p class="mt-2 text-sm text-gray-500">Classify the selected student as Senior High School or College graduating.</p>
                    </div>
                    <button @click="showModal = false" class="rounded-full bg-gray-100 p-2 text-gray-500 hover:bg-gray-200">×</button>
                </div>
                <div class="mt-6 rounded-3xl bg-gray-50 p-5">
                    <p class="text-sm font-medium text-gray-700">Student</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900" x-text="selectedStudentName"></p>
                </div>
                <div class="mt-6 grid gap-4">
                    <form method="POST" :action="`{{ url('/employee/clearance') }}/${selectedStudentId}/classify`" class="grid gap-4">
                        @csrf
                        <div class="flex gap-3">
                            <button type="submit" name="classification" value="senior_high_school" class="flex-1 rounded-3xl bg-sky-600 px-5 py-3 text-sm font-semibold text-white hover:bg-sky-700">Classify as SHS</button>
                            <button type="submit" name="classification" value="college_graduating" class="flex-1 rounded-3xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Classify as College</button>
                            <button type="submit" name="classification" value="unclassify" class="flex-1 rounded-3xl bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700">Unclassify</button>
                        </div>
                    </form>
                </div>
                <div class="mt-6 text-right">
                    <button type="button" @click="showModal = false" class="rounded-3xl bg-white border border-gray-200 px-5 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
