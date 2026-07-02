<div>
    @if($isOpen)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 p-4">
        <div class="bg-white w-full max-w-lg rounded-lg shadow-2xl overflow-hidden border border-gray-200">
            <div class="bg-blue-900 p-4 flex justify-between items-center">
                <h3 class="text-xs font-black text-white uppercase tracking-widest">Re-enroll Old Student</h3>
                <button wire:click="close" class="text-white opacity-50 hover:opacity-100 font-bold">✕</button>
            </div>

            <div class="p-6 space-y-4">
                {{-- Step 1: Pick Section --}}
                <div>
                    <label class="text-[9px] font-black text-gray-400 uppercase">1. Select Target Section</label>
                    <select wire:model="target_section_id" class="w-full border-gray-300 rounded text-xs font-bold uppercase mt-1">
                        <option value="">-- Choose Section --</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}">{{ $s->program->name }} » {{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('target_section_id') <span class="text-red-500 text-[8px] font-bold">{{ $message }}</span> @enderror
                </div>

                {{-- Step 2: Search Student --}}
                <div class="relative">
                    <label class="text-[9px] font-black text-gray-400 uppercase">2. Find Student Record</label>
                    <input type="text" wire:model.live.debounce.300ms="query" placeholder="Enter Name or ID..." class="w-full border-gray-300 rounded text-xs font-bold mt-1 uppercase">
                    
                    @if(!empty($results))
                    <div class="absolute z-10 w-full bg-white border rounded shadow-lg mt-1 max-h-48 overflow-y-auto">
                        @foreach($results as $student)
                        <button wire:click="enroll({{ $student->id }})" class="w-full text-left p-3 hover:bg-blue-50 border-b flex justify-between items-center group">
                            <div>
                                <p class="text-[10px] font-black uppercase text-gray-900">{{ $student->last_name }}, {{ $student->first_name }}</p>
                                <p class="text-[8px] text-gray-400">{{ $student->student_id }}</p>
                            </div>
                            <span class="text-[8px] font-black bg-blue-100 text-blue-700 px-2 py-1 rounded hidden group-hover:block">ENROLL NOW</span>
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            <div class="bg-gray-50 p-4 flex justify-end">
                <button wire:click="close" class="text-[10px] font-black uppercase text-gray-500 px-4 py-2 hover:text-gray-700">Cancel</button>
            </div>
        </div>
    </div>
    @endif
</div>