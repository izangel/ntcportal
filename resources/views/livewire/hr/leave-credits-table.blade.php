<div class="p-6 bg-white rounded-xl shadow-lg m-4 relative">
    
    {{-- Status Alerts Feedback --}}
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 text-green-800 rounded-lg border border-green-200 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Main View: Header Area --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 border-b border-gray-100 pb-5">
        <div>
            <h2 class="font-bold text-2xl text-gray-900 tracking-tight">Leave Credits Management</h2>
            <p class="text-sm text-gray-500 mt-0.5">Manage, allocate, and review annual employee leave balances.</p>
        </div>
        <div>
            <button wire:click="create" class="inline-flex items-center justify-center px-4 py-2 bg-indigo-600 rounded-lg font-semibold text-sm text-white shadow hover:bg-indigo-700 transition">
                + Assign Leave Credits
            </button>
        </div>
    </div>

    {{-- Sliding Drawer / Inline Form Toggle --}}
    @if($isFormOpen)
        <div class="mb-6 p-6 bg-gray-50 border border-gray-200 rounded-xl">
            <h3 class="text-lg font-bold text-gray-900 mb-4">
                {{ $isEditMode ? 'Modify Leave Balance Profile' : 'Assign New Leave Profile Balance' }}
            </h3>
            
            <form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Select Employee</label>
                    <select wire:model="employee_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 @error('employee_id') border-red-500 @enderror" {{ $isEditMode ? 'disabled' : '' }}>
                        <option value="">-- Choose Employee --</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                        @endforeach
                    </select>
                    @error('employee_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Academic Year</label>
                    <select wire:model="academic_year_id" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500 @error('academic_year_id') border-red-500 @enderror">
                        <option value="">-- Choose Academic Year --</option>
                        @foreach($academicYears as $year)
                            <option value="{{ $year->id }}">{{ $year->start_year }} - {{ $year->end_year }}</option>
                        @endforeach
                    </select>
                    @error('academic_year_id') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Sick Leave Balance (Days)</label>
                    <input type="number" step="0.5" wire:model="sick_leave" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                    @error('sick_leave') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Vacation Leave Balance (Days)</label>
                    <input type="number" step="0.5" wire:model="vacation_leave" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                    @error('vacation_leave') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Service Incentive Leave Balance (Days)</label>
                    <input type="number" step="0.5" wire:model="service_incentive_leave" class="w-full rounded-lg border-gray-300 text-sm focus:ring-indigo-500">
                    @error('service_incentive_leave') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 flex justify-end gap-2 mt-2">
                    <button type="button" wire:click="closeForm" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold text-sm text-gray-700 transition">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 rounded-lg font-semibold text-sm text-white shadow transition">
                        {{ $isEditMode ? 'Update Balances' : 'Save Balances' }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Filters Bar --}}
    <div class="flex flex-col md:flex-row gap-4 mb-6 bg-gray-50 p-4 rounded-xl border border-gray-150">
        <div class="flex-1">
            <input wire:model.live="search" type="text" placeholder="Search employee name..." class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        </div>
        <div>
            <select wire:model.live="selectedYear" class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm w-full">
                <option value="">All Academic Years</option>
                @foreach($academicYears as $year)
                    <option value="{{ $year->id }}">{{ $year->start_year }} - {{ $year->end_year }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Main Data Loop Table Container --}}
    <div class="overflow-x-auto relative">
        <div wire:loading class="absolute inset-0 bg-white/50 backdrop-blur-[1px] flex items-center justify-center z-10">
            <div class="animate-spin rounded-full h-7 w-7 border-b-2 border-indigo-600"></div>
        </div>

        <table class="w-full text-sm border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="p-3 text-left font-semibold text-gray-600 uppercase text-xs">Employee Details</th>
                    <th class="p-3 text-center font-semibold text-gray-600 uppercase text-xs">Sick Leave</th>
                    <th class="p-3 text-center font-semibold text-gray-600 uppercase text-xs">Vacation Leave</th>
                    <th class="p-3 text-center font-semibold text-gray-600 uppercase text-xs">Service Incentive</th>
                    <th class="p-3 text-left font-semibold text-gray-600 uppercase text-xs">Academic Year</th>
                    <th class="p-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($leavecredits as $leavecredit)
                    <tr class="hover:bg-gray-50/60 transition" wire:key="leave-credit-{{ $leavecredit->id }}">
                        <td class="p-3">
                            <span class="font-bold text-gray-900">{{ $leavecredit->employee->last_name }}, {{ $leavecredit->employee->first_name }}</span>
                            <div class="text-xs text-gray-400">ID: #{{ $leavecredit->employee->id }}</div>
                        </td>
                        <td class="p-3 text-center font-medium text-gray-800">{{ $leavecredit->sick_leave }} d</td>
                        <td class="p-3 text-center font-medium text-gray-800">{{ $leavecredit->vacation_leave }} d</td>
                        <td class="p-3 text-center font-medium text-gray-800">{{ $leavecredit->service_incentive_leave }} d</td>
                        <td class="p-3 text-gray-600">
                            <span class="px-2 py-0.5 bg-gray-100 border border-gray-200 rounded text-xs">
                                {{ $leavecredit->academicYear->start_year ?? 'N/A' }} - {{ $leavecredit->academicYear->end_year ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="p-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button wire:click="edit({{ $leavecredit->id }})" class="text-xs bg-gray-100 hover:bg-indigo-50 text-gray-700 hover:text-indigo-600 font-semibold px-2.5 py-1.5 rounded transition">
                                    Edit
                                </button>
                                <button onclick="confirm('Delete this record?') || event.stopImmediatePropagation()" wire:click="destroy({{ $leavecredit->id }})" class="text-xs bg-gray-100 hover:bg-red-50 text-gray-500 hover:text-red-600 font-semibold px-2.5 py-1.5 rounded transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400 font-medium">No records matching your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($leavecredits->hasPages())
        <div class="mt-4 pt-4 border-t border-gray-100">
            {{ $leavecredits->links() }}
        </div>
    @endif
</div>