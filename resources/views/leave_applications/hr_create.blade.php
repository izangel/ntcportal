@extends('layouts.admin')

@section('content')
<div class="py-12 bg-gray-100">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-indigo-900 border-b pb-4">HR: File Leave for Employee</h2>

            <form method="POST" action="{{ route('leave_applications.hr_store') }}">
            @csrf

            <div class="mb-6 p-4 bg-blue-50 border-l-4 border-blue-500">
                <label class="block text-sm font-bold text-blue-700">Filing leave for:</label>
                <select name="employee_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Select the Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                    @endforeach
                </select>
            </div>

            @include('leave_applications._form')

            <button type="submit">Submit as HR</button>
        </form>
        </div>
    </div>
</div>
@endsection