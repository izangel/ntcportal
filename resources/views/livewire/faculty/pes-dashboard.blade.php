<div class="space-y-6 max-w-5xl mx-auto p-6">

    <!-- Top Filtration Controls Header Card -->
    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-xs">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-end">
            <div>
                <h2 class="text-xl font-bold text-gray-800 tracking-tight">PES Submission Status Tracker</h2>
                <p class="text-xs text-gray-400 mt-1">Global directory ledger of hardcopy submissions status tracking.</p>
            </div>

            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Year</label>
                    <select wire:model.live="academic_year_id" class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Choose Year --</option>
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Semester</label>
                    <select wire:model.live="semester" class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="1st">1st Sem</option>
                        <option value="2nd">2nd Sem</option>
                        <option value="Summer">Summer</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Search Faculty Directory</label>
                    <input wire:model.live="search" type="text" placeholder="Type to filter..." class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500" />
                </div>
            </div>
        </div>
    </div>

    <!-- Personal Highlighted Notification Banner Section -->
    @if($currentFaculty)
        @php $isMyCopySubmitted = $mySubmission ? $mySubmission->is_submitted : false; @endphp
        
        @if($isMyCopySubmitted)
            <div class="bg-emerald-50 border border-emerald-200 p-4 rounded-xl flex items-center gap-3 shadow-2xs">
                <span class="p-1.5 bg-emerald-500 text-white rounded-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </span>
                <span class="text-xs text-emerald-800 font-medium">Your printed PES result hardcopy for this period has been processed and safely logged by Admin on: <strong>{{ $mySubmission->submitted_at->format('M d, Y h:i A') }}</strong></span>
            </div>
        @else
            <div class="bg-amber-50 border border-amber-200 p-4 rounded-xl flex items-center gap-3 shadow-2xs">
                <span class="p-1.5 bg-amber-500 text-white rounded-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </span>
                <span class="text-xs text-amber-800 font-medium">Your printed PES result hardcopy is currently marked as **Pending**. Please submit it directly to the office for verification logging.</span>
            </div>
        @endif
    @endif

    <!-- Shared Peer-to-Peer Progress Dashboard Ledger -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase text-xs tracking-wider">Faculty Employee</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-600 uppercase text-xs tracking-wider">Submission Status</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase text-xs tracking-wider">Date Logged</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($faculties as $faculty)
                        @php 
                            $submission = $faculty->pesSubmissions->first();
                            $isSubmitted = $submission ? $submission->is_submitted : false;
                            $isMe = $currentFaculty && ($currentFaculty->id === $faculty->id);
                        @endphp
                        <tr class="transition {{ $isMe ? 'bg-blue-50/50 hover:bg-blue-50' : 'hover:bg-gray-50/60' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-semibold {{ $isMe ? 'text-blue-900 font-bold' : 'text-gray-900' }}">
                                    {{ $faculty->last_name }}, {{ $faculty->first_name }} 
                                    @if($isMe) <span class="ml-1 text-[10px] bg-blue-600 text-white font-semibold px-1.5 py-0.5 rounded-full uppercase tracking-wide">You</span> @endif
                                </div>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-400 italic">No faculty employees found matching the filters for this evaluation period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-100">
            {{ $faculties->links() }}
        </div>
    </div>
</div>