<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Assign Program Heads') }}
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

            <div class="mb-4 bg-white overflow-hidden shadow-sm sm:rounded-lg p-5">
                <p class="text-sm text-gray-600">
                    Assign the Program Head who will <strong>check and review</strong> submitted syllabi for each program.
                    Re-assigning to a new head keeps the history of previous assignments; syllabi already reviewed stay
                    attributed to the head who signed them.
                </p>
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Program Head</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign New Head</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($programs as $program)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $program->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    @php $head = $program->currentHead(); @endphp
                                    @if ($head && $head->employee)
                                        {{ $head->employee->first_name }} {{ $head->employee->mid_name ? substr($head->employee->mid_name, 0, 1) . '. ' : '' }}{{ $head->employee->last_name }}
                                        <span class="text-xs text-gray-400">(since {{ $head->created_at->format('M d, Y') }})</span>
                                    @else
                                        <span class="text-xs italic text-gray-400">Not assigned</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <form action="{{ route('program-heads.store') }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="program_id" value="{{ $program->id }}">
                                        <select name="employee_id" required
                                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                            <option value="">Select employee...</option>
                                            @foreach ($employees as $id => $label)
                                                <option value="{{ $id }}" @selected($head && $head->employee_id === $id)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                        <x-button class="bg-indigo-600 hover:bg-indigo-700">Assign</x-button>
                                    </form>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if ($head)
                                        <form action="{{ route('program-heads.unassign', $program) }}" method="POST" onsubmit="return confirm('Remove the current program head?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs font-semibold text-red-600 hover:text-red-800">Remove</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500">No programs found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
