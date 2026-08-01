<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">OBE Outcome Structures Configuration</h1>
    </div>

    @if (session()->has('message'))
        <div class="p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm rounded-r-md shadow-xs">
            {{ session('message') }}
        </div>
    @endif

    <!-- Program & Batch Selector Banner -->
    <div class="bg-white p-6 rounded-xl shadow-xs border border-gray-200 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Select Academic Program</label>
            <select wire:model.live="selectedProgramId" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                <option value="">-- Choose Academic Program --</option>
                @foreach($programs as $program)
                    <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-2">Effective AY / Batch</label>
            <select wire:model.live="selectedBatchYear" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500 shadow-2xs">
                <option value="">All Batches</option>
                @foreach($batchOptions as $batchOption)
                    <option value="{{ $batchOption }}">Batch {{ $batchOption }}</option>
                @endforeach
            </select>
            <span class="text-[10px] text-gray-400 mt-1 block">New PEOs and POs use the selected batch by default.</span>
        </div>
    </div>

    @if($selectedProgramId)
        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'peo')" class="py-3 px-1 border-b-2 font-bold text-sm transition {{ $activeTab === 'peo' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    1. Program Educational Objectives (PEOs)
                </button>
                <button wire:click="$set('activeTab', 'po')" class="py-3 px-1 border-b-2 font-bold text-sm transition {{ $activeTab === 'po' ? 'border-indigo-600 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    2. Program Outcomes (POs / PLOs)
                </button>
            </nav>
        </div>

        @if($activeTab === 'peo')
            <!-- PEO SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Box -->
                <div class="bg-white p-6 rounded-xl shadow-xs border border-gray-200 h-fit" wire:key="peo-form-box-{{ $peoId ?? 'create' }}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-900">
                            {{ $peoId ? 'Edit PEO' : 'Add New PEO' }}
                        </h3>
                        @if($peoId)
                            <button type="button" wire:click="cancelEdit" class="text-xs text-rose-600 hover:underline font-bold">Cancel</button>
                        @endif
                    </div>

                    <form wire:submit.prevent="savePeo" class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PEO Code</label>
                            <input type="text" wire:model="peoCode" placeholder="e.g. PEO-01" class="w-full rounded-lg border-gray-300 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                            @error('peoCode') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Effective AY / Batch Year</label>
                            <input type="text" wire:model="peoEffectiveBatch" placeholder="Format: 2026 or 2026-2027" class="w-full rounded-lg border-gray-300 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Examples: <code>2026</code> or <code>2026-2027</code>. Leave blank for all cohorts.</span>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Description</label>
                            <textarea wire:model="peoDescription" rows="3" class="w-full rounded-lg border-gray-300 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('peoDescription') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Map to Institutional Goals</label>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1 border border-gray-200 rounded-lg p-2.5 bg-gray-50/50">
                                @foreach($institutionalGoals as $goal)
                                    <label class="flex items-start gap-2 text-xs text-gray-700 cursor-pointer" wire:key="goal-checkbox-{{ $goal->id }}">
                                        <input type="checkbox" wire:model="selectedGoals" value="{{ (string)$goal->id }}" class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                                        <span><strong>{{ $goal->code }}:</strong> {{ $goal->description }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-xs font-bold transition shadow-2xs">
                            {{ $peoId ? 'Update PEO' : 'Save PEO' }}
                        </button>
                    </form>
                </div>

                <!-- PEO List (Adding wire:key to each row) -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-xs border border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Existing PEOs</h3>
                    <div class="space-y-3">
                        @forelse($peos as $peo)
                            <div wire:key="peo-row-{{ $peo->id }}" class="p-4 border rounded-xl bg-gray-50/80 hover:bg-white transition space-y-2 border-gray-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-indigo-700 text-sm">{{ $peo->code }}</span>
                                        @if($peo->effective_batch_year)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                                AY {{ $peo->effective_batch_year }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gray-200 text-gray-700">
                                                All Batches
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="editPeo({{ $peo->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Edit</button>
                                        <button type="button" wire:click="deletePeo({{ $peo->id }})" wire:confirm="Are you sure you want to delete this PEO?" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                    </div>
                                </div>

                                <p class="text-gray-800 text-xs leading-relaxed">{{ $peo->description }}</p>

                                <div class="pt-1 flex flex-wrap gap-1">
                                    @foreach($peo->institutionalGoals as $g)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                            {{ $g->code }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center">
                                <p class="text-xs text-gray-400 italic">No PEOs configured for this selection.</p>
                                @if($previousBatchWithPeos)
                                    <button
                                        type="button"
                                        wire:click="carryForwardPeosFromPreviousBatch"
                                        wire:confirm="Carry forward PEOs (and their Institutional Goal mappings) from Batch {{ $previousBatchWithPeos }} to Batch {{ $selectedBatchYear }}?"
                                        class="mt-3 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2"/>
                                        </svg>
                                        Carry Forward PEOs from Batch {{ $previousBatchWithPeos }}
                                    </button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>

                
            </div>
        @else
            <!-- PO SECTION -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Form Box: Keyed by poId to force DOM re-render when switching edit targets -->
                <div class="bg-white p-6 rounded-xl shadow-xs border border-gray-200 h-fit" wire:key="po-form-container-{{ $poId ?? 'new' }}">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-base font-bold text-gray-900">
                            {{ $poId ? 'Edit Program Outcome' : 'Add New Program Outcome' }}
                        </h3>
                        @if($poId)
                            <button type="button" wire:click="cancelEdit" class="text-xs text-rose-600 hover:underline font-bold">Cancel</button>
                        @endif
                    </div>

                    <form wire:submit.prevent="savePo" class="space-y-4">
                        <!-- PO Code -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">PO Code</label>
                            <input type="text" 
                                wire:model="poCode" 
                                wire:key="po-code-input-{{ $poId ?? 'new' }}"
                                placeholder="e.g. PO-01" 
                                class="w-full rounded-lg border-gray-300 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                            @error('poCode') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Effective AY / Batch Year -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Effective AY / Batch Year</label>
                            <input type="text" 
                                wire:model="poEffectiveBatch" 
                                wire:key="po-batch-input-{{ $poId ?? 'new' }}"
                                placeholder="Format: 2026 or 2026-2027" 
                                class="w-full rounded-lg border-gray-300 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500">
                            <span class="text-[10px] text-gray-400 mt-0.5 block">Examples: <code>2026</code> or <code>2026-2027</code>. Leave blank for all cohorts.</span>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Description</label>
                            <textarea wire:model="poDescription" 
                                    wire:key="po-desc-input-{{ $poId ?? 'new' }}"
                                    rows="3" 
                                    class="w-full rounded-lg border-gray-300 text-xs shadow-2xs focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('poDescription') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Map to PEOs -->
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase mb-2">Map to PEOs</label>
                            <div class="space-y-1.5 max-h-40 overflow-y-auto pr-1 border border-gray-200 rounded-lg p-2.5 bg-gray-50/50">
                                @foreach($peos as $peo)
                                    <label class="flex items-start gap-2 text-xs text-gray-700 cursor-pointer" wire:key="po-peo-checkbox-{{ $poId ?? 'new' }}-{{ $peo->id }}">
                                        <input type="checkbox" 
                                            wire:model="selectedPeos" 
                                            value="{{ (string)$peo->id }}" 
                                            class="mt-0.5 rounded text-indigo-600 focus:ring-indigo-500">
                                        <span><strong>{{ $peo->code }}:</strong> {{ Str::limit($peo->description, 50) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-xs font-bold transition shadow-2xs">
                            {{ $poId ? 'Update Program Outcome' : 'Save Program Outcome' }}
                        </button>
                    </form>
                </div>

                <!-- PO List -->
                <div class="lg:col-span-2 bg-white p-6 rounded-xl shadow-xs border border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 mb-4">Existing Program Outcomes</h3>
                    <div class="space-y-3">
                        @forelse($pos as $po)
                            <div wire:key="po-list-card-{{ $po->id }}" class="p-4 border rounded-xl bg-gray-50/80 hover:bg-white transition space-y-2 border-gray-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-emerald-700 text-sm">{{ $po->code }}</span>
                                        @if($po->effective_batch_year)
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                AY {{ $po->effective_batch_year }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-gray-200 text-gray-700">
                                                All Batches
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button type="button" wire:click="editPo({{ $po->id }})" class="text-xs font-bold text-indigo-600 hover:text-indigo-800">Edit</button>
                                        <button type="button" wire:click="deletePo({{ $po->id }})" wire:confirm="Are you sure you want to delete this PO?" class="text-xs font-bold text-rose-600 hover:text-rose-800">Delete</button>
                                    </div>
                                </div>

                                <p class="text-gray-800 text-xs leading-relaxed">{{ $po->description }}</p>

                                <div class="pt-1 flex flex-wrap gap-1">
                                    @foreach($po->peos as $p)
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $p->code }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="py-4 text-center">
                                <p class="text-xs text-gray-400 italic">No Program Outcomes configured for this selection.</p>
                                @if($previousBatchWithPos)
                                    <button
                                        type="button"
                                        wire:click="carryForwardPosFromPreviousBatch"
                                        wire:confirm="Carry forward POs (and re-map their PEO links) from Batch {{ $previousBatchWithPos }} to Batch {{ $selectedBatchYear }}?"
                                        class="mt-3 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2"/>
                                        </svg>
                                        Carry Forward POs from Batch {{ $previousBatchWithPos }}
                                    </button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>