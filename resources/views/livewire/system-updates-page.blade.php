<div class="py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="text-center py-6">
            <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">System Updates</h1>
            <p class="text-gray-500 mt-2 max-w-2xl mx-auto">See the latest fixes, improvements and new features added to the portal.</p>
        </div>

        {{-- Category Filter --}}
        <div class="flex flex-wrap gap-2 justify-center">
            @foreach($categories as $cat)
                <button type="button" wire:click="setCategory('{{ $cat }}')"
                    class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-xs font-bold transition-colors
                    {{ $selectedCategory === $cat
                        ? 'bg-indigo-600 text-white'
                        : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                    <i class="fas {{ $cat === 'Bug Fix' ? 'fa-bug' : 'fa-star' }}"></i>
                    {{ $cat }}
                </button>
            @endforeach
            @if($selectedCategory)
                <button type="button" wire:click="clearCategory"
                    class="inline-flex items-center gap-1 rounded-full px-3 py-1.5 text-xs font-bold text-gray-500 hover:text-gray-700">
                    <i class="fas fa-xmark"></i> Clear
                </button>
            @endif
        </div>

        {{-- Updates List --}}
        <div class="space-y-4">
            @forelse($updates as $update)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center gap-3 flex-wrap mb-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold {{ $update->category === 'Bug Fix' ? 'bg-rose-50 text-rose-700' : ($update->category === 'Improvement' ? 'bg-sky-50 text-sky-700' : 'bg-emerald-50 text-emerald-700') }}">
                            <i class="fas {{ $update->category === 'Bug Fix' ? 'fa-bug' : ($update->category === 'Improvement' ? 'fa-arrows-rotate' : 'fa-star') }}"></i>
                            {{ $update->category }}
                        </span>
                        <span class="text-xs text-gray-400">
                            <i class="fas fa-calendar-day mr-1"></i>{{ $update->release_date?->format('F j, Y') }}
                        </span>
                    </div>
                    <h2 class="text-lg font-bold text-gray-900">{{ $update->title }}</h2>
                    <p class="text-sm text-gray-600 mt-2 whitespace-pre-line">{{ $update->description }}</p>
                </div>
            @empty
                <div class="text-center py-20 bg-white rounded-xl border-2 border-dashed border-gray-200">
                    <i class="fas fa-circle-info text-3xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No {{ $selectedCategory ? strtolower($selectedCategory) . ' updates' : 'updates' }} yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>