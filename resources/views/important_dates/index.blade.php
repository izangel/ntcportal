@extends('layouts.admin')



@push('scripts')
    <style>
        /* Pinakonti natin ang CSS. Ito na lang ang naiwan para sa Page layout at Table borders */

        .print-only-header {

            display: none;

        }



        @media print {

            @page {

                margin: 20mm;

            }



            * {

                -webkit-print-color-adjust: exact !important;

                print-color-adjust: exact !important;

            }



            header,

            aside,

            nav,

            form,

            button,

            a {

                display: none !important;

            }



            body,

            main,

            .max-w-7xl,

            .py-12,

            .bg-white {

                width: 100% !important;

                max-width: 100% !important;

                margin: 0 !important;

                padding: 0 !important;

                box-shadow: none !important;

                background-color: white !important;

            }



            table {

                width: 100% !important;

                border-collapse: collapse !important;

                margin-top: 10px !important;

            }



            th,

            td {

                border: 1px solid #000 !important;

                padding: 10px 8px !important;

                text-align: left !important;

            }



            .web-only-title {

                display: none !important;

            }



            .print-only-header {

                display: block !important;

            }

        }
    </style>
@endpush



@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight web-only-title">

        {{ __('Important Dates & Events') }}

    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                {{-- Header Actions --}}
                <div class="flex justify-between items-center mb-6 web-only-title">
                    <h3 class="text-lg font-medium text-gray-900">Academic & Administrative Calendar</h3>
                    <div class="flex items-center space-x-3">
                        {{-- PRINTABLE CALENDAR BUTTON: Admin, Teacher, HR, Academic Head, at Staff --}}
                        @if (in_array(auth()->user()->role, ['admin', 'teacher', 'hr', 'academic_head', 'staff']))
                            <button onclick="window.print()"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                <i class="fas fa-print mr-2 text-indigo-600 text-sm"></i> Printable Calendar
                            </button>
                        @endif

                        {{-- ADD NEW DATE --}}
                        @if (auth()->user()->role === 'admin' || auth()->user()->role === 'teacher')
                            <a href="{{ route('important_dates.create') }}"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Post New Date
                            </a>
                        @endif
                    </div>
                </div>
                
                {{-- Alert Notifications --}}
                @if (session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 web-only-title"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                {{-- Filter Form --}}
                <form action="{{ route('important_dates.index') }}" method="GET"
                    class="mb-4 bg-gray-50 p-4 rounded-md shadow-sm border border-gray-100 web-only-title">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="category_id" class="block font-medium text-sm text-gray-700">Filter by
                                Category</label>
                            <select id="category_id" name="category_id"
                                class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">All Categories</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex items-end space-x-2">
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('Apply Filters') }}
                            </button>
                            <a href="{{ route('important_dates.index') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Clear') }}
                            </a>
                        </div>
                    </div>
                </form>

                {{-- 🖨️ CUSTOM HEADER PARA SA PRINTING (Perfectly Centered with Logo) --}}
                <div class="print-only-header" style="width: 100%; text-align: center; margin-bottom: 20px;">
                    <h1 class="font-serif text-2xl font-black text-black text-center uppercase m-0 p-0">
                        NORTHLINK TECHNOLOGICAL COLLEGE
                    </h1>
                    <h2
                        style="font-size: 16px; font-weight: normal; color: #333; margin: 5px 0 15px 0; padding: 0; text-align: center; text-transform: uppercase; letter-spacing: 1px;">
                        Academic & Administrative Calendar
                    </h2>
                    <div style="border-bottom: 2px solid black; width: 100%; margin-bottom: 15px;"></div>
                </div>
                {{-- Data Table --}}
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                {{-- Itatago sa print: Status --}}
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">
                                    Status
                                </th>
                                {{-- Ipi-print: Event Date --}}
                                <th class="px-6 py-3 text-center text-sm font-bold text-black uppercase tracking-wider">
                                    Date
                                </th>
                                {{-- Ipi-print: Title --}}
                                <th class="px-6 py-3 text-center text-sm font-bold text-black uppercase tracking-wider">
                                    Title
                                </th>
                                {{-- Itatago sa print: Categories, Posted By, Actions --}}
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">
                                    Categories
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">
                                    Posted By
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider print:hidden">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($dates as $date)
                                <tr>
                                    {{-- Status Data - Itatago sa print --}}
                                    <td class="px-6 py-4 whitespace-nowrap print:hidden">
                                        @php
                                            $now = now()->startOfDay();
                                            $start = $date->start_date->startOfDay();
                                            $end = ($date->end_date ?? $date->start_date)->startOfDay();
                                        @endphp
                                        @if ($now->between($start, $end))
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 border border-green-200">
                                                <span class="w-2 h-2 mr-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                                ONGOING
                                            </span>
                                        @elseif ($now->lt($start))
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200">
                                                UPCOMING
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-500 border border-gray-200">
                                                PASSED
                                            </span>
                                        @endif
                                    </td>
                                    {{-- Event Date Data - I-pi-print ito --}}
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600 print:text-black">
                                        {{ $date->formatted_date }}
                                    </td>
                                    {{-- Title Data - I-pi-print ito --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{-- Ginaya ang font style ng Event Date --}}
                                        <div class="text-sm font-bold text-indigo-600 print:text-black">{{ $date->title }}
                                        </div>
                                        {{-- Itatago yung maliit na description kapag nag-print --}}
                                        <div class="text-sm text-black truncate max-w-xs print:hidden">
                                            {{ $date->description }}</div>
                                    </td>
                                    {{-- Categories Data - Itatago sa print --}}
                                    <td class="px-6 py-4 whitespace-nowrap print:hidden">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($date->categories as $cat)
                                                <span
                                                    class="px-2 py-0.5 text-xs font-semibold rounded-full bg-blue-50 text-blue-700 border border-blue-100">
                                                    {{ $cat->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    {{-- Posted By Data - Itatago sa print --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 print:hidden">
                                        {{ $date->author->name ?? 'System' }}
                                    </td>
                                    {{-- Actions Data - Itatago sa print --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium print:hidden">
                                        @if (auth()->user()->role === 'admin' || auth()->id() === $date->user_id)
                                            <a href="{{ route('important_dates.edit', $date) }}"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                            <form action="{{ route('important_dates.destroy', $date) }}" method="POST"
                                                class="inline-block" onsubmit="return confirm('Delete this event?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="text-red-600 hover:text-red-900">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No important
                                        dates found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Pagination --}}
                <div class="mt-4 web-only-title">
                    {{ $dates->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
