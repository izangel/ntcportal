@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                Memos & Advisories
            </h1>
            <p class="text-gray-500 mt-1">
                Official institutional tracking, directives, and announcements.
            </p>
        </div>

        @if(auth()->check() && (auth()->id() === 1 || auth()->user()->hasRole('admin')))
            <a href="{{ route('admin.memos.create') }}"
               class="inline-flex items-center px-5 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                <i class="fas fa-plus mr-2"></i>
                Create New Advisory
            </a>
        @endif
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- Advisories Table --}}
    <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">

        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-gray-50">
                <tr class="text-xs uppercase tracking-wider text-gray-600">
                    <th class="px-6 py-4 text-left">Advisory No.</th>
                    <th class="px-6 py-4 text-left">Subject</th>
                    <th class="px-6 py-4 text-left">To</th>
                    <th class="px-6 py-4 text-left">From</th>
                    <th class="px-6 py-4 text-left">Date</th>
                    <th class="px-6 py-4 text-center">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">

            @forelse($advisories as $advisory)

                <tr class="hover:bg-gray-50 transition">

                    {{-- Advisory Number --}}
                    <td class="px-6 py-5 font-semibold text-blue-600">
                        {{ $advisory->advisory_no }}
                    </td>

                    {{-- Subject --}}
                    <td class="px-6 py-5">
                        <div class="font-semibold text-gray-900">
                            {{ $advisory->subject }}
                        </div>

                        <div class="text-sm text-gray-500 mt-1">
                            {{ \Illuminate\Support\Str::limit(strip_tags($advisory->body), 60) }}
                        </div>
                    </td>

                    {{-- Target Audience --}}
                    <td class="px-6 py-5">

                        @php

                            $targets = $advisory->to;

                            // Convert NULL to empty array
                            if (is_null($targets)) {
                                $targets = [];
                            }

                            // Convert JSON string to array
                            elseif (is_string($targets)) {
                                $decoded = json_decode($targets, true);

                                $targets = is_array($decoded)
                                    ? $decoded
                                    : [];
                            }

                            // Safety check
                            elseif (!is_array($targets)) {
                                $targets = [];
                            }

                            $labels = [
                                'all_students' => 'All Students',
                                'all_staff' => 'All Staff',
                                'all_shs_faculty' => 'SHS Faculty',
                                'all_college_faculty' => 'College Faculty',
                                'admin_personnel' => 'Admin Personnel',
                                'specific_personnel' => 'Specific Personnel',
                            ];

                        @endphp

                        @if(count($targets))

                            @foreach($targets as $target)

                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-100 text-gray-700 text-xs mr-1 mb-1">
                                    {{ $labels[$target] ?? ucfirst(str_replace('_',' ',$target)) }}
                                </span>

                            @endforeach

                        @else

                            <span class="text-gray-400 italic">
                                No target specified
                            </span>

                        @endif

                    </td>

                    {{-- From --}}
                    <td class="px-6 py-5">
                        {{ $advisory->from }}
                    </td>

                    {{-- Date --}}
                    <td class="px-6 py-5">
                        {{ optional($advisory->date)->format('M d, Y') }}
                    </td>

                    {{-- Actions --}}
                    <td class="px-6 py-5">

                        <div class="flex justify-center gap-4">

                            <a href="{{ route('admin.memos.show', $advisory) }}"
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                                Read
                            </a>

                              @if(auth()->check() && (auth()->id() === 1 || auth()->user()->hasRole('admin')))

                                <form action="{{ route('admin.memos.destroy', $advisory->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Delete this advisory?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="text-red-600 hover:text-red-800">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>

                                </form>

                            @endif

                        </div>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="6" class="text-center py-16">

                        <i class="fas fa-folder-open text-5xl text-gray-300 mb-4"></i>

                        <p class="text-gray-500">
                            No advisories found.
                        </p>

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-6">
     {{-- {{ $advisories->links() }} --}}
    </div>

</div>
@endsection