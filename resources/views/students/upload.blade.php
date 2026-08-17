{{-- resources/views/students/upload.blade.php --}}

@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Upload Students') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-xl font-semibold text-gray-800 mb-6">Upload Student CSV File</h3>

                <div class="mb-6 rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                    <p class="font-semibold mb-1">CSV Requirements</p>
                    <p class="mb-1">The CSV must include a header row with these columns:</p>
                    <ul class="ml-4 list-disc list-inside space-y-0.5">
                        <li><code class="bg-blue-100 px-1 rounded">first_name</code> (required)</li>
                        <li><code class="bg-blue-100 px-1 rounded">last_name</code> (required)</li>
                        <li><code class="bg-blue-100 px-1 rounded">email</code> (optional — if blank, a login account is generated as <code class="bg-blue-100 px-1 rounded">lastnamefirstname@northlink.edu.ph</code>)</li>
                        <li><code class="bg-blue-100 px-1 rounded">middle_name</code> (optional)</li>
                        <li><code class="bg-blue-100 px-1 rounded">contact_number</code> (optional)</li>
                        <li><code class="bg-blue-100 px-1 rounded">student_id_name</code> (optional — display label for the student's ID, separate from the system's <code class="bg-blue-100 px-1 rounded">student_id</code>)</li>
                        <li><code class="bg-blue-100 px-1 rounded">birthday</code> (optional — <code class="bg-blue-100 px-1 rounded">YYYY-MM-DD</code>)</li>
                        <li><code class="bg-blue-100 px-1 rounded">password</code> (optional — defaults to <code class="bg-blue-100 px-1 rounded">northlink</code>)</li>
                    </ul>
                    <p class="mt-1">A student user account is created automatically for each row and linked to the student record. Students already registered are skipped.</p>
                </div>

                {{-- Success message with icon --}}
                @if (session('success'))
                    <div class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-50 dark:bg-gray-800 dark:text-green-400" role="alert">
                        <svg class="flex-shrink-0 w-4 h-4 mr-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm9.5 9.5a9.5 9.5 0 1 1-19 0 9.5 9.5 0 0 1 19 0Z" />
                        </svg>
                        <span class="sr-only">Success</span>
                        <div class="text-sm font-medium">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif
                
                {{-- Error message with icon and bullet points --}}
                @if ($errors->any())
                    <div class="flex items-start p-4 mb-4 text-red-800 rounded-lg bg-red-50 dark:bg-gray-800 dark:text-red-400" role="alert">
                        <svg class="flex-shrink-0 w-4 h-4 mr-2 mt-0.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3A1.5 1.5 0 0 1 9.5 4ZM12 15H8a1 1 0 0 1 0-2h1v-2H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v3h1a1 1 0 0 1 0 2Z" />
                        </svg>
                        <span class="sr-only">Info</span>
                        <div class="text-sm font-medium">
                            <span class="font-bold">Oops!</span> There were some problems with your submission.
                            <ul class="mt-2 ml-4 list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">Choose CSV File:</label>
                        <input type="file" name="csv_file" id="csv_file" accept=".csv" required
                               class="block w-full text-sm text-gray-500
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-full file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-violet-50 file:text-violet-700
                                      hover:file:bg-violet-100
                                      focus:outline-none focus:ring-2 focus:ring-violet-500">
                        @error('csv_file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm">
                        Upload and Import Students
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection