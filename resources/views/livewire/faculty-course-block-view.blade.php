<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">📝 Course Grading Portal</h2>
   
    <hr class="my-6">
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-indigo-500">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Select Academic Period</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div>
                <label for="ay" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="sem" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
            </div>
            
            <div class="p-3 bg-indigo-50 rounded-lg text-sm text-indigo-700 flex items-center">
                <p class="font-semibold">Select your filters to view assigned blocks.</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-purple-500">
        @if ($academicYearId && $semester)
            <label for="block" class="block text-sm font-medium text-gray-700 mb-2">
                Select Course Block to Grade ({{ $assignedBlocks->count() }} found):
            </label>
            
            @if ($assignedBlocks->isNotEmpty())
                <div class="flex items-center space-x-4">
                    <select id="block" wire:model.live="selectedBlockId" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500">
                        <option value="">-- Select a Block --</option>
                        @foreach ($assignedBlocks as $block)
                            <option value="{{ $block->id }}">
                                {{ $block->course->code }} - {{ $block->course->name }} ({{ $block->schedule_string }}) (Room: {{ $block->room_name }})
                            </option>
                        @endforeach
                    </select>

                    @if ($selectedBlockId && !$blockSelectedAndConfirmed)
                        <button wire:click="loadSelectedBlockGrades" 
                                wire:loading.attr="disabled"
                                class="px-6 py-2 whitespace-nowrap bg-purple-600 text-white font-semibold rounded-md shadow hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150">
                            <span wire:loading.remove wire:target="loadSelectedBlockGrades">Load Grades</span>
                            <span wire:loading wire:target="loadSelectedBlockGrades">Loading...</span>
                        </button>
                    @elseif ($selectedBlockId && $blockSelectedAndConfirmed)
                        <span class="px-6 py-2 text-sm text-green-600 border border-green-300 rounded-md bg-green-50 whitespace-nowrap">Grades Loaded</span>
                    @endif
                </div>
            @else
                <p class="text-gray-500 italic">No blocks assigned to you for the selected Academic Period.</p>
            @endif
        @else
            <p class="text-gray-500 italic">Please select both Academic Year and Semester to view your assigned blocks.</p>
        @endif
    </div>

    {{-- 🔑 Only render the child components IF blockSelectedAndConfirmed is TRUE 🔑 --}}
    @if ($blockSelectedAndConfirmed && $selectedBlockId)
        @php
            // Find the selected block object and determine status
            $selectedBlock = $assignedBlocks->firstWhere('id', $selectedBlockId);
            $isFinalized = $selectedBlock ? $selectedBlock->finalized : false;
        @endphp

        {{-- 🔑 Use $syncKey to force reload after INC resolution 🔑 --}}
        @livewire('grade-input-form', 
            ['blockId' => $selectedBlockId], 
            key('grade-input-' . $selectedBlockId . '-' . $syncKey)
        )

       
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{!! session('message') !!}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{!! session('error') !!}</p>
            </div>
        @endif
        

        @if ($isFinalized)
            <h3 class="text-2xl font-bold text-red-700 mb-4 mt-8">Finalized Course Block: INC Resolution Interface</h3>
            @livewire('resolve-inc-grade', 
                ['blockId' => $selectedBlockId], 
                key('resolve-inc-' . $selectedBlockId)
            )
        @endif
    @else
        <p class="text-center text-gray-500 mt-10 p-6 bg-white shadow-lg rounded-xl">
            @if ($selectedBlockId)
                Click the **"Load Grades"** button to view the enrollment data and begin grading for the selected block.
            @else
                Select a Course Block above to begin grading.
            @endif
        </p>
    @endif
    
</div>