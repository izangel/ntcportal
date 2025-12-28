{{-- resources/views/announcements/edit.blade.php --}}
@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Announcement</h2>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white p-6 shadow-xl sm:rounded-lg">
            <form action="{{ route('announcements.update', $announcement) }}" method="POST">
                @csrf
                @method('PUT') {{-- This is crucial for updates --}}

                <div class="mb-4">
                    <label class="block text-gray-700">Title</label>
                    <input type="text" name="title" value="{{ old('title', $announcement->title) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Category / Department</label>
                    <select name="category" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                        @foreach(\App\Models\Announcement::$categories as $cat)
                            <option value="{{ $cat }}" {{ old('category', $announcement->category) == $cat ? 'selected' : '' }}>
                                {{ $cat }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700">Content</label>
                    <textarea name="body" rows="5" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">{{ old('body', $announcement->body) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_pinned" value="1" {{ $announcement->is_pinned ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                        <span class="ml-2 text-gray-600">Pin this announcement</span>
                    </label>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <a href="{{ route('announcements.index') }}" class="px-4 py-2 bg-gray-200 rounded-md">Cancel</a>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md shadow-sm">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection