<!-- resources/views/livewire/admin/obe-setup.blade.php -->
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">OBE Outcome Structures Configuration</h1>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 text-sm">
            {{ session('message') }}
        </div>
    @endif

    <!-- Program Selector -->
    <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-2">Select Program to Configure</label>
        <select wire:model.live="selectedProgramId" class="w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">-- Choose Academic Program --</option>
            @foreach($programs as $program)
                <option value="{{ $program->id }}">{{ $program->code }} - {{ $program->name }}</option>
            @endforeach
        </select>
    </div>

    @if($selectedProgramId)
        <!-- Dynamic Tabs -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button wire:click="$set('activeTab', 'peo')" class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'peo' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    1. Program Educational Objectives (PEOs)
                </button>
                <button wire:click="$set('activeTab', 'po')" class="py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab === 'po' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}">
                    2. Program Outcomes (POs / PLOs)
                </button>
            </nav>
        </div>

        @if($activeTab === 'peo')
            <!-- PEO Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Add New PEO</h3>
                    <form wire:submit.prevent="savePeo" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PEO Code</label>
                            <input type="text" wire:model="peoCode" placeholder="PEO-01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="peoDescription" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Map to Institutional Goals</label>
                            @foreach($institutionalGoals as $goal)
                                <label class="flex items-center space-x-2 text-sm text-gray-600 mb-1">
                                    <input type="checkbox" wire:model="selectedGoals" value="{{ $goal->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span><strong>{{ $goal->code }}:</strong> {{ $goal->description }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium shadow-sm">Save PEO</button>
                    </form>
                </div>

                <!-- PEO List -->
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Existing PEOs</h3>
                    <div class="space-y-4">
                        @forelse($peos as $peo)
                            <div class="p-4 border rounded-md bg-gray-50">
                                <div class="font-bold text-indigo-600">{{ $peo->code }}</div>
                                <p class="text-gray-700 text-sm mt-1">{{ $peo->description }}</p>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($peo->institutionalGoals as $g)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $g->code }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No PEOs configured for this program yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @else
            <!-- PO Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Add New Program Outcome</h3>
                    <form wire:submit.prevent="savePo" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">PO Code</label>
                            <input type="text" wire:model="poCode" placeholder="PO-01" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea wire:model="poDescription" rows="3" class="mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Map to PEOs</label>
                            @foreach($peos as $peo)
                                <label class="flex items-center space-x-2 text-sm text-gray-600 mb-1">
                                    <input type="checkbox" wire:model="selectedPeos" value="{{ $peo->id }}" class="rounded text-indigo-600 focus:ring-indigo-500">
                                    <span><strong>{{ $peo->code }}:</strong> {{ Str::limit($peo->description, 50) }}</span>
                                </label>
                            @endforeach
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-md text-sm font-medium shadow-sm">Save Program Outcome</button>
                    </form>
                </div>

                <!-- PO List -->
                <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800">Existing Program Outcomes</h3>
                    <div class="space-y-4">
                        @forelse($pos as $po)
                            <div class="p-4 border rounded-md bg-gray-50">
                                <div class="font-bold text-indigo-600">{{ $po->code }}</div>
                                <p class="text-gray-700 text-sm mt-1">{{ $po->description }}</p>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach($po->peos as $p)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                            {{ $p->code }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No Program Outcomes configured yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>