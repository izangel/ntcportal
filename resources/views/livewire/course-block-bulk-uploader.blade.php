<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">⬆️ Bulk Course Block Upload Tool</h2>
    
    {{-- --- CONTEXT SELECTION BLOCK --- --}}
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-indigo-500">
        <h3 class="text-xl font-semibold mb-4 text-gray-700">1. Select Academic Context</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label for="ay" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option>
                    @endforeach
                </select>
                @error('academicYearId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="sem" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }}</option>
                    @endforeach
                </select>
                @error('semester') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    </div>
    
    {{-- --- MESSAGES (Error, Success, Warnings) --- --}}
    @if (session()->has('error') && !session()->has('bulk_errors'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    
    @if (session()->has('message'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
            <p>{!! session('message') !!}</p>
        </div>
    @endif
    
    @if (session()->has('bulk_errors'))
        <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4 rounded" role="alert">
            <p class="font-bold">⚠️ Upload Warnings: Some rows were skipped or had issues.</p>
            <ul class="list-disc list-inside mt-2 text-sm max-h-48 overflow-y-auto">
                @foreach (session('bulk_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- --- BULK UPLOAD FORM BLOCK (Conditional) --- --}}
    @if ($showUploadForm)
        <div class="bg-white shadow-lg rounded-xl p-6 border-t-4 border-teal-500">
            <h3 class="text-xl font-semibold mb-4 text-gray-700">2. Upload CSV File</h3>
            
            <p class="text-sm text-red-600 mb-4 font-semibold">
                **Required CSV Columns:** `section_id`, `course_id`, `faculty_id`, `room_name`, `schedule_string`. 
                (Use database ID's. Context: **AY {{ $ay_name }}** / **{{ $semester }}**)
            </p>

            <form wire:submit.prevent="bulkUploadCourseBlocks">
                <div class="flex items-center space-x-4">
                    <input 
                        type="file" 
                        wire:model="csvFile" 
                        id="csvFile" 
                        accept=".csv, text/csv"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                    >
                    
                    <button 
                        type="submit" 
                        class="whitespace-nowrap py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white transition duration-150 ease-in-out 
                            {{ $uploading ? 'bg-teal-400 cursor-not-allowed' : 'bg-teal-600 hover:bg-teal-700' }}"
                        @if ($uploading || !$csvFile) disabled @endif
                    >
                        @if ($uploading)
                            Processing...
                        @else
                            Process CSV
                        @endif
                    </button>
                </div>
                
                @error('csvFile') 
                    <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> 
                @enderror

                {{-- Progress Bar --}}
                <div x-data="{ isUploading: false, progress: 0 }"
                    x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false; progress = 0"
                    x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                >
                    <div x-show="isUploading" class="mt-2 w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-teal-600 h-2.5 rounded-full" :style="`width: ${progress}%`"></div>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="p-6 bg-white shadow-lg rounded-xl border-l-4 border-gray-400 text-gray-600">
            <p class="font-bold">Awaiting Selection:</p>
            <p>Please select both the **Academic Year** and **Semester** above to enable the CSV upload form.</p>
        </div>
    @endif
</div>