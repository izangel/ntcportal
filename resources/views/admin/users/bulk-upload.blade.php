@extends('admin.admin')

@section('content')
<div class="max-w-4xl mx-auto mt-8">
    <div class="mb-6">
        <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Bulk Student Onboarding</h1>
        <p class="text-gray-600">Import multiple student accounts via CSV file.</p>
    </div>

    @if(session('success'))
        <div class="flex p-4 mb-6 text-sm text-green-800 border border-green-300 rounded-xl bg-green-50 shadow-sm" role="alert">
            <svg class="flex-shrink-0 inline w-4 h-4 me-3 mt-[2px]" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white border border-gray-100 rounded-2xl shadow-xl overflow-hidden">
            <form action="{{ route('admin.bulk-upload.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                
                <div class="flex items-center justify-center w-full">
                    <label for="csv_file" class="flex flex-col items-center justify-center w-full h-64 border-2 border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all duration-300 border-indigo-200 hover:border-indigo-400">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <div class="p-4 bg-indigo-100 rounded-full mb-4 text-indigo-600">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                            </div>
                            <p class="mb-2 text-sm text-gray-700"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                            <p class="text-xs text-gray-500 uppercase tracking-wider font-bold text-indigo-400">CSV files only</p>
                        </div>
                        <input id="csv_file" name="csv_file" type="file" class="hidden" accept=".csv" />
                    </label>
                </div>

                <div class="mt-8 flex items-center justify-between">
                    <div class="text-sm text-gray-500 italic" id="file-name-display">
                        No file selected
                    </div>
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                        Process Upload
                        <svg class="ml-2 -mr-1 w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 h-fit">
            <h3 class="text-indigo-900 font-bold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                CSV Format Guide
            </h3>
            <p class="text-sm text-indigo-800 mb-4 leading-relaxed">
                Ensure your file includes the following headers in order:
            </p>
            <ul class="space-y-3">
                <li class="flex items-center text-xs font-mono bg-white p-2 rounded border border-indigo-200">
                    <span class="w-4 h-4 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-2 text-[10px]">1</span> email
                </li>
                <li class="flex items-center text-xs font-mono bg-white p-2 rounded border border-indigo-200">
                    <span class="w-4 h-4 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-2 text-[10px]">2</span> role
                </li>
                <li class="flex items-center text-xs font-mono bg-white p-2 rounded border border-indigo-200">
                    <span class="w-4 h-4 bg-indigo-600 text-white rounded-full flex items-center justify-center mr-2 text-[10px]">3</span> password
                </li>
                
            </ul>
            <div class="mt-6 pt-6 border-t border-indigo-200">
                <p class="text-[11px] text-indigo-600 font-medium">Tip: Use "student" for the role and "northlink" as the default password if left blank.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // Simple script to show the file name when selected
    document.getElementById('csv_file').onchange = function () {
        document.getElementById('file-name-display').innerHTML = this.files[0].name;
    };
</script>
@endsection