<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Syllabus Review — Program Head</h1>
        <p class="text-sm text-gray-600">Submitted syllabi from your program(s) that need your check and review.</p>
    </div>

    @if(count($pending) > 0)
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-indigo-200 bg-indigo-50 px-4 py-3">
            <i class="fas fa-bell text-indigo-600"></i>
            <p class="text-sm text-indigo-900">
                <strong>{{ count($pending) }}</strong> submitted syllabus{{ count($pending) === 1 ? '' : 'es' }} waiting for your check and review.
            </p>
        </div>
    @endif

    @if(count($pending) === 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
            <p class="text-sm text-gray-500">No syllabi awaiting your review. You're all caught up.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Schedule</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pending as $row)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-bold text-gray-900">{{ $row['course_code'] }}</span>
                                    <span class="block text-xs text-gray-500">{{ $row['course_name'] }}</span>
                                    @if($row['revision_remarks'])
                                        <span class="mt-1.5 inline-flex items-start gap-1 rounded bg-amber-50 border border-amber-200 px-2 py-1 text-[11px] text-amber-800">
                                            <i class="fas fa-comment-dots mt-0.5"></i>
                                            <span>
                                                <strong>Re-sent for revision ({{ $row['revision_requested_by'] ?: 'unknown' }}):</strong>
                                                {{ $row['revision_remarks'] }}
                                            </span>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['program'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['faculty'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['schedule'] }} • {{ $row['semester'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['submitted_at'] }}</td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('faculty.syllabus.print', [$row['block_id'], $row['program_id']]) }}"
                                            target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Preview</a>
                                        <button wire:click="openRequestChanges({{ $row['id'] }})"
                                            class="px-4 py-1.5 bg-amber-500 text-white rounded-md text-xs font-bold hover:bg-amber-600">
                                            Request Changes
                                        </button>
                                        <button wire:click="openReview({{ $row['id'] }})"
                                            class="px-4 py-1.5 bg-indigo-600 text-white rounded-md text-xs font-bold hover:bg-indigo-700">
                                            Check &amp; Review
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

        @if($signingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" wire:click="cancelReview"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl p-6">
                <h3 class="text-base font-bold text-gray-900">Check &amp; Review Syllabus</h3>
                <p class="mt-1 text-sm text-gray-500">Type your full name below to indicate that you have checked and reviewed this syllabus.</p>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name (Signature)</label>
                    <input type="text" wire:model="signatureName"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('signatureName')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="cancelReview" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">Cancel</button>
                    <button wire:click="confirmReview" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700">Sign &amp; Confirm</button>
                </div>
            </div>
        </div>
    @endif

    @if($requestingId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/40" wire:click="cancelRequestChanges"></div>
            <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl p-6">
                <h3 class="text-base font-bold text-gray-900">Request Changes</h3>
                <p class="mt-1 text-sm text-gray-500">
                    This will return the syllabus to the teacher. The syllabus will be <strong>unlocked</strong> so they can edit it,
                    and it will need to be resubmitted for another review.
                </p>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Remarks for the teacher</label>
                    <textarea wire:model="remarks" rows="4"
                        placeholder="What changes are needed?..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    @error('remarks')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="cancelRequestChanges" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">Cancel</button>
                    <button wire:click="confirmRequestChanges" class="px-4 py-2 bg-amber-500 text-white rounded-md text-sm font-bold hover:bg-amber-600">Return for Revision</button>
                </div>
            </div>
        </div>
    @endif
</div>
