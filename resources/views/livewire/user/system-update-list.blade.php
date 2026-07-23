<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Portal Updates</h1>
        <p class="text-gray-500">Stay up to date with the latest features and fixes.</p>
    </div>

    <div class="space-y-6">
        @forelse($updates as $update)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-200 p-6">
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1 text-xs font-medium rounded-full {{ $update->category === 'Bug Fix' ? 'bg-red-50 text-red-700' : 'bg-green-50 text-green-700' }}">
                            {{ $update->category }}
                        </span>
                        <h2 class="text-xl font-bold text-gray-900">{{ $update->title }}</h2>
                        <span class="text-sm text-gray-400">{{ $update->version_number }}</span>
                    </div>
                    <p class="text-gray-600 mb-4 whitespace-pre-line">{{ $update->description }}</p>
                    <div class="flex items-center text-sm text-gray-400">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Released on {{ \Carbon\Carbon::parse($update->release_date)->format('M d, Y') }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-20 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                <p class="text-gray-500">No updates yet.</p>
            </div>
        @endforelse
    </div>
</div>
