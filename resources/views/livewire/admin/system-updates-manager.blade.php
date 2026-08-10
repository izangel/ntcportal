<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">System Updates</h1>
                <p class="text-sm text-gray-600 mt-1">Share recent fixes, improvements and new features with everyone.</p>
            </div>
            <button type="button" wire:click="openCreate"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <i class="fas fa-plus"></i>
                Post New Update
            </button>
        </div>

        @if (session()->has('system-update-message'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                <i class="fas fa-check-circle mr-2"></i>{{ session('system-update-message') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @forelse($updates as $update)
                <div class="flex items-start justify-between gap-4 p-5 border-b border-gray-100 last:border-0 hover:bg-gray-50/60 transition-colors">
                    <div class="flex items-start gap-4 min-w-0">
                        <span class="flex-shrink-0 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-[11px] font-bold {{ $update->category === 'Bug Fix' ? 'bg-rose-50 text-rose-700' : ($update->category === 'Improvement' ? 'bg-sky-50 text-sky-700' : 'bg-emerald-50 text-emerald-700') }}">
                            <i class="fas {{ $update->category === 'Bug Fix' ? 'fa-bug' : ($update->category === 'Improvement' ? 'fa-arrows-rotate' : 'fa-star') }}"></i>
                            {{ $update->category }}
                        </span>
                        <div class="min-w-0">
                            <h3 class="font-bold text-gray-900">{{ $update->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1 whitespace-pre-line">{{ $update->description }}</p>
                            <p class="text-xs text-gray-400 mt-2">
                                <i class="fas fa-calendar-day mr-1"></i>{{ $update->release_date?->format('M d, Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button type="button" wire:click="openEdit({{ $update->id }})"
                            class="p-2 text-gray-400 hover:text-indigo-600 transition-colors" aria-label="Edit">
                            <i class="fas fa-pen"></i>
                        </button>
                        <button type="button" wire:click="confirmDelete({{ $update->id }})"
                            class="p-2 text-gray-400 hover:text-rose-600 transition-colors" aria-label="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @empty
                <div class="text-center py-16">
                    <i class="fas fa-circle-info text-3xl text-gray-300 mb-3"></i>
                    <p class="text-gray-500">No updates posted yet.</p>
                </div>
            @endforelse
        </div>

        {{-- Create / Edit Modal --}}
        @if($isModalOpen)
            <div class="fixed inset-0 z-50 overflow-y-auto" wire:key="modal-{{ $editingId ?? 'new' }}">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <div class="fixed inset-0 bg-gray-800/60 transition-opacity" wire:click="closeModal"></div>
                    <div class="relative inline-block w-full max-w-xl transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:align-middle">
                        <div class="px-6 py-5 border-b border-gray-100">
                            <h3 class="text-lg font-bold text-gray-900">{{ $editingId ? 'Edit System Update' : 'Post System Update' }}</h3>
                            <p class="text-sm text-gray-500 mt-0.5">Fill in the details below.</p>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Title</label>
                                <input type="text" wire:model="title" placeholder="e.g., Gradebook loading improved"
                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('title') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Category</label>
                                    <select wire:model="category" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat }}">{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                    @error('category') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Release Date</label>
                                    <input type="date" wire:model="release_date" class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('release_date') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1">Description</label>
                                <textarea wire:model="description" rows="4" placeholder="Explain what has changed..."
                                    class="w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('description') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeModal" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 transition-colors">Cancel</button>
                            <button type="button" wire:click="save" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white hover:bg-indigo-700 transition-colors">
                                {{ $editingId ? 'Update' : 'Publish Update' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Delete Confirmation Modal --}}
        @if($confirmDeleteId)
            <div class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <div class="fixed inset-0 bg-gray-800/60 transition-opacity" wire:click="cancelDelete"></div>
                    <div class="relative inline-block w-full max-w-sm transform overflow-hidden rounded-xl bg-white text-left align-bottom shadow-xl transition-all sm:align-middle">
                        <div class="px-6 py-5">
                            <h3 class="text-lg font-bold text-gray-900">Delete this update?</h3>
                            <p class="text-sm text-gray-500 mt-1">This action cannot be undone.</p>
                        </div>
                        <div class="bg-gray-50 px-6 py-4 flex items-center justify-end gap-3">
                            <button type="button" wire:click="cancelDelete" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">Cancel</button>
                            <button type="button" wire:click="delete" class="rounded-lg bg-rose-600 px-4 py-2 text-sm font-bold text-white hover:bg-rose-700 transition-colors">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>