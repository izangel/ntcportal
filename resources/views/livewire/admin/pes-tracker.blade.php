<div class="p-6 bg-white rounded-xl border border-gray-200 shadow-sm">
    <!-- Header Controls Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-end border-b pb-6 mb-6">
        
        <!-- Left Title Context Column -->
        <div class="lg:col-span-1">
            <h2 class="text-xl font-bold text-gray-800 tracking-tight">PES Submission Tracker</h2>
            <p class="text-xs text-gray-400 mt-1">Verify and catalog physical copy documentation.</p>
        </div>

        <!-- Right Side Dynamic Interactive Filters -->
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Your Exact Layout Format: Year Selection -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Year</label>
                <select wire:model.live="academic_year_id" class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Choose Year --</option>
                    @foreach($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Your Exact Layout Format: Semester Selection -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Semester</label>
                <select wire:model.live="semester" class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="1st">1st Sem</option>
                    <option value="2nd">2nd Sem</option>
                    <option value="Summer">Summer</option>
                </select>
            </div>

            <!-- Search Field Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Search Faculty</label>
                <input wire:model.live="search" type="text" placeholder="Type to search..." class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500" />
            </div>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="mb-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <!-- Data Table Ledger -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase text-xs tracking-wider">Faculty Employee</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600 uppercase text-xs tracking-wider">Submission Status</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase text-xs tracking-wider">Date Logged</th>
                    <th class="px-6 py-3 text-center font-semibold text-gray-600 uppercase text-xs tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($faculties as $faculty)
                    @php 
                        $submission = $faculty->pesSubmissions->first();
                        $isSubmitted = $submission ? $submission->is_submitted : false;
                    @endphp
                    <tr class="hover:bg-gray-50/60 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-semibold text-gray-900">{{ $faculty->first_name }} {{ $faculty->last_name }}</div>
                            <div class="text-xs text-gray-400 font-normal">{{ $faculty->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($isSubmitted)
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">
                                    Submitted
                                </span>
                            @else
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                                    Pending Copy
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 text-xs">
                            {{ $isSubmitted && $submission->submitted_at ? $submission->submitted_at->format('M d, Y h:i A') : '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <button 
                                wire:click="toggleSubmission({{ $faculty->id }})" 
                                class="px-3 py-1.5 text-xs font-semibold rounded-lg shadow-2xs border transition-all {{ $isSubmitted ? 'bg-rose-50 hover:bg-rose-100 text-rose-600 border-rose-200' : 'bg-blue-600 hover:bg-blue-700 text-white border-transparent' }}"
                            >
                                {{ $isSubmitted ? 'Mark Unsubmitted' : 'Mark Received' }}
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">No faculty employees scheduled under this specific academic matrix configuration.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="mt-4">
        {{ $faculties->links() }}
    </div>
</div>