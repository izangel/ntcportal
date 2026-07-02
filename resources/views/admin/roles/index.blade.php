<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Employee Roles') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            @if (session('success'))
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm rounded-r-lg" role="alert">
                    <p class="font-bold">Success</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-sm rounded-r-lg" role="alert">
                    <p class="font-bold">Please correct the following:</p>
                    <ul class="list-disc ml-5 mt-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mb-8 bg-white overflow-hidden shadow-xl sm:rounded-lg p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Add New System Role</h3>
                <form action="{{ route('roles.store') }}" method="POST" class="flex flex-col sm:flex-row gap-4">
                    @csrf
                    <div class="flex-1">
                        <input type="text" name="name" placeholder="e.g., registrar, dean, hr_manager" 
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                        <p class="mt-1 text-xs text-gray-500 italic">Spaces will be converted to underscores automatically.</p>
                    </div>
                    <div>
                        <x-button class="w-full sm:w-auto bg-gray-800 hover:bg-gray-700">
                            {{ __('Add Role') }}
                        </x-button>
                    </div>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Employee Role Assignment</h3>
                    <p class="text-sm text-gray-600">Assign multiple system permissions to faculty and staff.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Designation</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">System Roles</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($users as $user)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                       <div class="text-sm font-bold text-gray-900">
                                                {{ $user->employee->last_name }}, {{ $user->employee->first_name }}
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 uppercase">
                                            {{ $user->employee->role ?? 'No Designation' }}
                                        </span>
                                    </td>

                                    <form action="{{ route('roles.update', $user->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <td class="px-6 py-4">
                                            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                                                @foreach($roles as $role)
                                                    <label class="inline-flex items-center group cursor-pointer">
                                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 h-4 w-4"
                                                            {{ $user->roles->contains($role->id) ? 'checked' : '' }}>
                                                        <span class="ml-2 text-xs text-gray-700 group-hover:text-indigo-600 transition-colors capitalize">
                                                            {{ str_replace('_', ' ', $role->name) }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <x-button class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs uppercase tracking-widest">
                                                {{ __('Save') }}
                                            </x-button>
                                        </td>
                                    </form>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                        No employees found in the system.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>