<div class="mb-6 p-4 bg-blue-900 rounded-lg shadow-lg relative">
    <label class="text-[10px] font-black text-blue-300 uppercase tracking-widest mb-2 block">Quick Re-enroll Old Student</label>
    
    <div class="space-y-2">
        {{-- Section Picker --}}
        <select wire:model="target_section_id" class="w-full bg-blue-800 border-none rounded px-3 py-2 text-[10px] font-bold text-white outline-none mb-2">
            <option value="">Select Target Section...</option>
            @foreach($sections as $s)
                <option value="{{ $s->id }}">{{ $s->program->name }} » {{ $s->name }}</option>
            @endforeach
        </select>

        {{-- Search Input --}}
        <div class="relative">
            <input type="text" 
                   wire:model.live.debounce.300ms="query" 
                   placeholder="Type Name or Student ID..." 
                   class="w-full bg-white border-none rounded px-3 py-2 text-xs font-bold focus:ring-2 focus:ring-amber-500 outline-none">
            
            {{-- Dropdown Results --}}
            @if(!empty($results))
                <div class="absolute z-50 mt-1 w-full bg-white rounded shadow-xl border border-gray-200 overflow-hidden">
                    @foreach($results as $student)
                        <button wire:click="enrollStudent({{ $student->id }})" 
                                class="w-full text-left px-4 py-3 hover:bg-amber-50 border-b border-gray-100 transition flex justify-between items-center">
                            <div>
                                <p class="text-xs font-black text-gray-900 uppercase">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                <p class="text-[9px] text-gray-400 font-bold uppercase">{{ $student->student_id }}</p>
                            </div>
                            <span class="text-[8px] font-black bg-blue-100 text-blue-700 px-2 py-1 rounded">ADD TO SEMESTER</span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
    
    <p class="text-[8px] text-blue-400 mt-2 italic">Selecting a student will immediately assign them to the section and sync their schedule.</p>
</div>