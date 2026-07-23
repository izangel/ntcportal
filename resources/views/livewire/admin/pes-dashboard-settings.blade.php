<div class="bg-white p-6 rounded-xl border border-indigo-100 shadow-sm max-w-xl">
    <h3 class="text-sm font-black text-indigo-900 uppercase tracking-widest mb-2 flex items-center gap-2">
        ⚙️ PES Dashboard Display Settings
    </h3>
    <p class="text-xs text-gray-500 mb-4">
        Set the target academic period that will display by default on all Faculty and Staff Dashboards.
    </p>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-50 text-green-700 text-xs font-bold rounded-lg border border-green-200">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="saveSettings" class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Target Academic Year</label>
                <select wire:model="academic_year_id" class="w-full text-xs font-medium rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Year...</option>
                    @foreach($academicYears as $year)
                        <option value="{{ $year->id }}">SY {{ $year->start_year }}-{{ $year->end_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-[10px] font-bold uppercase text-gray-400 mb-1">Target Semester</label>
                <select wire:model="semester" class="w-full text-xs font-medium rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="1st">1st Semester</option>
                    <option value="2nd">2nd Semester</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>
        </div>

        <div class="pt-2 flex justify-end">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-xs font-bold uppercase tracking-widest rounded-md hover:bg-indigo-700 transition">
                Apply to Dashboard
            </button>
        </div>
    </form>
</div>