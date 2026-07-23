<div class="p-6 space-y-8 max-w-7xl mx-auto">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-800">Administrative Monitoring Console: PES Tracking</h1>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search across roster names..." class="border rounded p-2 text-sm w-64">
    </div>

    <!-- Tracking Cards Matrix -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- COMPLETED MODULE LOG -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-green-100">
            <h2 class="text-lg font-bold text-green-700 border-b pb-2 mb-4">
                Completed Submissions ({{ $completedTeachers->count() }})
            </h2>
            <div class="overflow-y-auto max-h-96 space-y-2">
                @forelse($completedTeachers as $teacher)
                    <div class="p-3 bg-gray-50 rounded flex justify-between items-center text-sm">
                        <div>
                            <p class="font-semibold text-gray-800">{{ $teacher->name }}</p>
                            <p class="text-xs text-gray-500">Last Action: {{ $teacher->pesSubmissions->first()->created_at->format('M d, Y') }}</p>
                        </div>
                       
                        <a href="{{ route('pes.download', $teacher->pesSubmissions->first()) }}" target="_blank" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200">
                            Inspect File
                        </a>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm italic">No confirmed records found matching parameters.</p>
                @endforelse
            </div>
        </div>

        <!-- PENDING/MISSING MODULE LOG -->
        <div class="bg-white p-6 rounded-lg shadow-sm border border-red-100">
            <h2 class="text-lg font-bold text-red-700 border-b pb-2 mb-4">
                Pending/Not Done ({{ $missingTeachers->count() }})
            </h2>
            <div class="overflow-y-auto max-h-96 space-y-2">
                @forelse($missingTeachers as $teacher)
                    <div class="p-3 bg-gray-50 rounded flex justify-between items-center text-sm">
                        <span class="font-semibold text-gray-800">{{ $teacher->name }}</span>
                        <span class="text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded">
                            Action Required
                        </span>
                    </div>
                @empty
                    <p class="text-gray-400 text-sm italic">Excellent! Roster compliance stands at 100%.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>