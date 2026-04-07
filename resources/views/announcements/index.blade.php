@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('School Announcements') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                {{-- Header Section --}}
                <div class="flex justify-between items-center mb-8 border-b pb-4">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Latest Updates</h3>
                        <p class="text-sm text-gray-500">Stay informed about school activities and news.</p>
                    </div>
                    
                    {{-- Only show "Post" button to Admins/Teachers --}}
                    @can('post-announcements')
                    <a href="{{ route('announcements.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Post New Announcement
                    </a>
                    @endcan
                </div>

                {{-- Feedback Messages --}}
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Filter Form --}}
                <form action="{{ route('announcements.index') }}" method="GET" class="mb-8 bg-gray-50 p-4 rounded-md border border-gray-200">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <label for="category_filter" class="block text-sm font-medium text-gray-700">Filter by Department/Category</label>
                            <select name="category" id="category_filter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500">
                                <option value="">All Categories</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mt-5 flex gap-2">
                            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm">Filter</button>
                            <a href="{{ route('announcements.index') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm">Clear</a>
                        </div>
                    </div>
                </form>

                
                {{-- Announcements Feed --}}
                <div class="space-y-6">
                    @forelse ($announcements as $announcement)
                        <div class="relative bg-gray-50 p-6 rounded-xl border {{ $announcement->is_pinned ? 'border-indigo-300 bg-indigo-50' : 'border-gray-200' }} shadow-sm">
                            
                            {{-- Pinned Badge --}}
                            @if($announcement->is_pinned)
                                <div class="absolute top-0 right-0 mt-2 mr-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                        📌 Pinned
                                    </span>
                                </div>
                            @endif

                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $announcement->category_color }} mr-2 uppercase tracking-wider">
                                {{ $announcement->category }}
                            </span>

                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                    {{-- Find the title inside your loop and change it to this --}}
                                    <h4 class="text-xl font-bold text-gray-900 mb-1 hover:text-indigo-600 transition">
                                        <a href="{{ route('announcements.show', $announcement) }}">
                                            {{ $announcement->title }}
                                        </a>
                                    </h4>
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <span class="font-medium text-indigo-600 mr-2">{{ $announcement->author->name }}</span>
                                        <span class="mr-2">&bull;</span>
                                        <span>{{ $announcement->created_at->format('M d, Y h:i A') }}</span>
                                        <span class="ml-2">({{ $announcement->created_at->diffForHumans() }})</span>
                                    </div>
                                </div>
                            </div>

                           

                            {{-- Action Buttons for Staff --}}
                            @can('post-announcements')
                                <div class="flex justify-end space-x-4 border-t pt-4 border-gray-200">
                                    <a href="{{ route('announcements.edit', $announcement) }}" class="text-indigo-600 hover:text-indigo-900 text-sm font-semibold">Edit</a>
                                    
                                    <form action="{{ route('announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-semibold">Delete</button>
                                    </form>
                                </div>
                            @endcan
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No announcements</h3>
                            <p class="mt-1 text-sm text-gray-500">Check back later for new updates from the school.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                <div class="mt-8">
                    {{ $announcements->links() }}
                </div>

            </div>
        </div>
    </div>
@endsection