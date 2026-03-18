@extends('admin.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Post New Announcement') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                {{-- Form Header --}}
                <div class="mb-6 border-b pb-4">
                    <h3 class="text-lg font-medium text-gray-900">Create a New Update</h3>
                    <p class="text-sm text-gray-500">This will be visible to all students, teachers, and administrators.</p>
                </div>

                {{-- Error Messages (if any from validation) --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <strong class="font-bold">Whoops!</strong> Something went wrong.
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('announcements.store') }}" method="POST">
                    @csrf

                    {{-- Title Input --}}
                    <div class="mb-6">
                        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('title') border-red-300 @enderror" 
                               placeholder="e.g., Midterm Exam Schedule">
                        @error('title')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label for="category" class="block text-sm font-medium text-gray-700">Category / Department</label>
                        <select name="category" id="category" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                            @foreach(\App\Models\Announcement::$categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
        
                    {{-- Body Textarea --}}
                    <div class="mb-6">
                        <label for="body" class="block text-sm font-medium text-gray-700">Content / Body</label>
                        <textarea name="body" id="body" rows="10" required
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('body') border-red-300 @enderror" 
                                  placeholder="Write the details of the announcement here...">{{ old('body') }}</textarea>
                        @error('body')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-500 text-xs mt-2">Line breaks will be preserved when displayed.</p>
                    </div>

                    {{-- Pinned Status Checkbox --}}
                    <div class="mb-8">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="is_pinned" name="is_pinned" type="checkbox" value="1" {{ old('is_pinned') ? 'checked' : '' }}
                                       class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="is_pinned" class="font-medium text-gray-700">Pin this announcement</label>
                                <p class="text-gray-500">Pinned announcements stay at the top of the feed and are highlighted.</p>
                            </div>
                        </div>
                    </div>

                    {{-- Form Actions --}}
                    <div class="flex items-center justify-end gap-3 border-t pt-6">
                        <a href="{{ route('announcements.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Cancel') }}
                        </a>
                        
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            {{ __('Post Announcement') }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection