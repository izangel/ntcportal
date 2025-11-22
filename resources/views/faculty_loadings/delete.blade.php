@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Delete Faculty Loading') }}
    </h2>
@endsection

@section('content')

<div class="max-w-3xl mx-auto p-6 bg-white shadow-xl rounded-lg">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Confirm Delete</h2>

    <p class="mb-4 text-gray-700">Are you sure you want to delete this faculty loading? This action cannot be undone.</p>

    <div class="space-y-2 mb-6">
        <div><strong>Academic Year:</strong> {{ $loading->academicYear->start_year }} - {{ $loading->academicYear->end_year }}</div>
        <div><strong>Semester:</strong> {{ $loading->semester }}</div>
        <div><strong>Course:</strong> {{ $loading->course->code }} - {{ $loading->course->name }}</div>
        <div><strong>Section:</strong> {{ $loading->section->program->name }}-{{ $loading->section->name }}</div>
        <div><strong>Faculty:</strong> {{ $loading->faculty->last_name }}, {{ $loading->faculty->first_name }} {{ $loading->faculty->mid_name }}</div>
        <div><strong>Room:</strong> {{ $loading->room }}</div>
        <div><strong>Schedule:</strong> {{ $loading->schedule }}</div>
    </div>

    <form method="POST" action="{{ route('faculty-loadings.destroy', $loading->id) }}">
        @csrf
        @method('DELETE')

        <div class="flex justify-between">
            <a href="{{ route('faculty-loadings.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">Yes, Delete</button>
        </div>
    </form>
</div>

@endsection
