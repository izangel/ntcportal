@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add Position') }}
        </h2>
        <a href="{{ route('admin.positions.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden p-8">
            <form action="{{ route('admin.positions.store') }}" method="POST">
                @csrf

                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Position Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            placeholder="e.g. President">
                        @error('name') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="program_type" class="block text-sm font-medium text-gray-700">Program Type</label>
                        <select name="program_type" id="program_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="both" {{ old('program_type') == 'both' ? 'selected' : '' }}>Both (SHS & College)</option>
                            <option value="shs" {{ old('program_type') == 'shs' ? 'selected' : '' }}>SHS Only</option>
                            <option value="college" {{ old('program_type') == 'college' ? 'selected' : '' }}>College Only</option>
                        </select>
                        @error('program_type') <p class="text-sm text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" id="is_active" value="1" checked
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="is_active" class="text-sm text-gray-700">Active</label>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        <i class="fas fa-save mr-1"></i> Save Position
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
