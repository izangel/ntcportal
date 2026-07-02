<div>
    {{-- Dynamic Flash Notification Banner Stacks --}}
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Filter/Search Interface Bar --}}
    <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-100 shadow-inner">
        <div>
            <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Live Search Keyword</label>
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Name or email..." class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase text-gray-600 mb-1">Filter by Active Role</label>
            <select wire:model.live="role" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All Roles</option>
                @foreach($roles as $r)
                    <option value="{{ $r }}">{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end justify-end">
            <a href="{{ route('employees.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition">
                + Add New Employee
            </a>
        </div>
    </div>

    {{-- Contextual Bulk Actions Bar Panel --}}
    @if(count($selectedEmployees) > 0)
        <div class="mb-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4 transition-all duration-200">
            <div class="flex items-center">
                <span class="text-sm font-bold text-indigo-900">
                    Selected Rows: <span class="bg-indigo-600 text-white font-extrabold px-2 py-0.5 rounded-full text-xs ml-1 shadow-sm">{{ count($selectedEmployees) }}</span>
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                {{-- Mass Role Modification controls --}}
                <div class="flex items-center shadow-sm">
                    <select wire:model="bulkRole" class="rounded-l-md border-gray-300 text-xs focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">-- Change Role To --</option>
                        @foreach($roles as $r)
                            <option value="{{ $r }}">{{ ucwords(str_replace('_', ' ', $r)) }}</option>
                        @endforeach
                    </select>
                    <button type="button" wire:click="bulkUpdateRole" class="px-3 py-2 bg-indigo-600 text-white font-bold text-xs rounded-r-md hover:bg-indigo-700 transition">
                        Apply Role Change
                    </button>
                </div>

                {{-- Mass Destructive Soft-Deletion --}}
                <button type="button" wire:click="bulkDelete" onclick="return confirm('Are you completely sure you want to mass archive all {{ count($selectedEmployees) }} selected employee accounts?')" class="px-4 py-2 bg-red-600 text-white font-bold text-xs rounded-md hover:bg-red-700 shadow transition">
                    Bulk Delete / Archive
                </button>
            </div>
        </div>
    @endif

    {{-- Main Livewire Data Table Frame --}}
    <div class="overflow-x-auto border border-gray-200 rounded-lg shadow-sm">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left w-12">
                        <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">First Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Middle Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Linked User</th>
                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" wire:loading.class="opacity-60">
                @forelse ($employees as $employee)
                    <tr class="hover:bg-gray-50/50 transition @if(in_array($employee->id, $selectedEmployees)) bg-indigo-50/30 @endif" wire:key="emp-row-{{ $employee->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" value="{{ $employee->id }}" wire:model.live="selectedEmployees" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->last_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $employee->first_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $employee->middle_name ?? '--' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $employee->email ?? 'N/A' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="px-2 py-0.5 text-xs rounded bg-slate-100 font-medium text-slate-800 uppercase tracking-wider">
                                {{ ucwords(str_replace('_', ' ', $employee->role)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($employee->user)
                                <span class="text-green-600 font-medium text-xs">{{ $employee->user->email }}</span>
                            @else
                                <span class="text-gray-400 italic text-xs">Not Linked</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if ($employee->user)
                                <form action="{{ route('employees.reset-password', $employee) }}" method="POST" class="inline-block mr-3" onsubmit="return confirm('Are you sure you want to reset the password for {{ $employee->user->email }}?')">
                                    @csrf
                                    <button type="submit" class="text-amber-600 hover:text-amber-800 font-semibold">Reset Password</button>
                                </form>
                            @endif
                            <a href="{{ route('employees.show', $employee) }}" class="text-blue-600 hover:text-blue-900 mr-3">View</a>
                            <a href="{{ route('employees.edit', $employee) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this employee?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-10 text-center italic text-sm text-gray-400 bg-gray-50/50">
                            No matching employee accounts found matching selection choices.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $employees->links() }}
    </div>
</div>