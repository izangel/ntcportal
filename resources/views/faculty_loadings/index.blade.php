@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Faculty Loading') }}
    </h2>
@endsection

@section('content')

<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Faculty Loadings</h2>
        <a href="{{ route('faculty-loadings.create') }}" 
           class="px-4 py-2 bg-green-600 text-white font-semibold rounded-md shadow-md hover:bg-green-700 transition">
            + Add New Loading
        </a>
    </div>

    <div class="overflow-x-auto bg-white shadow-xl rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acad Year/Sem</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Section</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room</th>
                    <th class="relative px-6 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($loadings as $load)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">{{ $load->academicYear->start_year }} - {{ $load->academicYear->end_year }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-medium">{{ $load->course->code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $load->section->program->name }}-{{ $load->section->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $load->faculty->last_name}}-{{ $load->faculty->first_name}}-{{ $load->faculty->mid_name}}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $load->schedule }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $load->room }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('faculty-loadings.edit', $load->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a> | 
                            <a href="{{ route('faculty-loadings.delete', $load->id) }}" class="text-red-600 hover:text-red-900">Delete</a>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection