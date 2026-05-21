@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <div>
            <h2 class="font-bold text-3xl text-gray-900 leading-tight tracking-tight">
                {{ __('Student Clearance Review') }}
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="text-sm text-gray-400 font-medium">| Review clearance details for the selected student</span>
            </div>
        </div>
        <div>
            <a href="{{ route('employee.clearance.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition">
                Back to Students
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="py-10 bg-gray-50/50 min-h-screen">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8">
            <div class="mb-6 flex items-center justify-between gap-4">
                <a href="{{ route('employee.clearance.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 text-sm font-semibold rounded-full hover:bg-gray-200 transition">
                    &larr; Back to Students
                </a>
                @if(session('status'))
                    <div class="rounded-3xl bg-green-50 border border-green-200 p-5 text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('employee.clearance.review.submit', $student) }}">
                @csrf

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Student Information</h3>
                        <dl class="space-y-4 text-sm text-gray-700">
                            <div>
                                <dt class="font-semibold">Student ID</dt>
                                <dd>{{ $student->student_id ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold">Name</dt>
                                <dd>{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold">Email</dt>
                                <dd>{{ optional($student->user)->email ?? $student->email ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="font-semibold">Section</dt>
                                <dd>{{ optional($student->section)->name ?? 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Assigned Clearance Office</h3>
                        <div class="rounded-3xl bg-gray-50 border border-gray-200 p-6">
                            @if(!empty($employeeDeptOffice))
                                <p class="text-gray-700">{{ $employeeDeptOffice->name }}</p>
                            @else
                                <p class="text-gray-500">Department/Office not set</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-6 lg:grid-cols-2">
                    <div>
                        <label for="status" class="block text-sm font-semibold text-gray-700">Clearance Decision</label>
                        <select id="status" name="status" class="mt-2 block w-full rounded-3xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="sign" {{ old('status', $currentStatus ?? 'sign') === 'sign' ? 'selected' : '' }}>Sign clearance</option>
                            <option value="reject" {{ old('status', $currentStatus ?? 'sign') === 'reject' ? 'selected' : '' }}>Reject clearance</option>
                        </select>
                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-semibold text-gray-700">Clearance Notes</label>
                        <textarea id="notes" name="notes" rows="5" class="mt-2 block w-full rounded-3xl border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $currentNotes ?? '') }}</textarea>
                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-full bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        Save Clearance Decision
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection