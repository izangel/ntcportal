@extends('admin.admin')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Announcement Details') }}
        </h2>
        <a href="{{ route('announcements.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
            &larr; Back to all announcements
        </a>
    </div>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                
                {{-- Category and Date --}}
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $announcement->category_color }} uppercase tracking-wider mr-3">
                            {{ $announcement->category }}
                        </span>
                        @if($announcement->is_pinned)
                            <span class="text-indigo-600 text-sm font-semibold">📌 Pinned Post</span>
                        @endif
                    </div>
                    <span class="text-sm text-gray-500 italic">
                        Posted {{ $announcement->created_at->format('M d, Y') }} at {{ $announcement->created_at->format('h:i A') }}
                    </span>
                </div>

                {{-- Title --}}
                <h1 class="text-3xl font-extrabold text-gray-900 mb-4 leading-tight">
                    {{ $announcement->title }}
                </h1>

                {{-- Author Info --}}
                <div class="flex items-center mb-8 pb-8 border-b border-gray-100">
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                        {{ substr($announcement->author->name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $announcement->author->name }}</p>
                        <p class="text-xs text-gray-500">Authorized Personnel</p>
                    </div>
                </div>

                {{-- Main Body --}}
                <div class="text-gray-700 text-lg leading-relaxed whitespace-pre-wrap mb-10">
                    {!! nl2br(e($announcement->body)) !!}
                </div>

                {{-- Management Actions (Visible only to Staff) --}}
                @can('post-announcements')
                    <div class="mt-12 pt-6 border-t border-gray-200 flex justify-end gap-4">
                        <a href="{{ route('announcements.edit', $announcement) }}" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-semibold hover:bg-gray-50">
                            Edit Post
                        </a>
                        <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" onsubmit="return confirm('Delete this announcement permanently?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-50 text-red-600 border border-red-200 px-4 py-2 rounded-md text-sm font-semibold hover:bg-red-100">
                                Delete
                            </button>
                        </form>
                    </div>
                @endcan
            </div>
        </div>
    </div>
@endsection