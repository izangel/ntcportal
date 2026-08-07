<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Syllabus Approval — Academic Head</h1>
        <p class="text-sm text-gray-600">Submitted syllabi awaiting your approval. Items without a Program Head review are syllabi whose teacher is also the assigned Program Head (self-review is blocked), so they come straight to you.</p>
    </div>

    @if(count($revisions) > 0)
        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">In Revision (returned to teacher)</h2>
        <div class="bg-white rounded-lg shadow-sm border border-amber-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested At</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarks</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($revisions as $row)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-bold text-gray-900">{{ $row['course_code'] }}</span>
                                    <span class="block text-xs text-gray-500">{{ $row['course_name'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['program'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['faculty'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['requested_by'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['requested_at'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['remarks'] }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('faculty.syllabus.print', [$row['block_id'], $row['program_id']]) }}"
                                        target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Preview</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Pending Approval</h2>
    @if(count($pending) > 0)
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
            <i class="fas fa-bell text-emerald-600"></i>
            <p class="text-sm text-emerald-900">
                <strong>{{ count($pending) }}</strong> syllabus{{ count($pending) === 1 ? '' : 'es' }} awaiting your approval.
            </p>
        </div>
    @endif
    @if(count($pending) === 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center mb-8">
            <p class="text-sm text-gray-500">No syllabi awaiting your approval.</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewed By</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reviewed At</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($pending as $row)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-bold text-gray-900">{{ $row['course_code'] }}</span>
                                    <span class="block text-xs text-gray-500">{{ $row['course_name'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['program'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['faculty'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['reviewed_by'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['reviewed_at'] }}</td>
                                <td class="px-5 py-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('faculty.syllabus.print', [$row['block_id'], $row['program_id']]) }}"
                                            target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Preview</a>
                                        <button wire:click="openApprove({{ $row['id'] }})"
                                            class="px-4 py-1.5 bg-emerald-600 text-white rounded-md text-xs font-bold hover:bg-emerald-700">
                                            Approve
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

    @if(count($approved) > 0)
        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-3">Already Approved</h2>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Faculty</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved By</th>
                            <th class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved At</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($approved as $row)
                            <tr>
                                <td class="px-5 py-4">
                                    <span class="text-sm font-bold text-gray-900">{{ $row['course_code'] }}</span>
                                    <span class="block text-xs text-gray-500">{{ $row['course_name'] }}</span>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['program'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['faculty'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['approved_by'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700">{{ $row['approved_at'] }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('faculty.syllabus.print', [$row['block_id'], $row['program_id']]) }}"
                                        target="_blank" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">Preview</a>
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
            <div class="absolute inset-0 bg-black/40" wire:click="cancelApprove"></div>
            <div class="relative w-full max-w-md rounded-xl bg-white shadow-xl p-6">
                <h3 class="text-base font-bold text-gray-900">Approve Syllabus</h3>
                <p class="mt-1 text-sm text-gray-500">Type your full name below to indicate that you have approved this syllabus.</p>
                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Full Name (Signature)</label>
                    <input type="text" wire:model="signatureName"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    @error('signatureName')
                        <span class="mt-1 block text-xs text-red-600">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button wire:click="cancelApprove" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md text-sm font-semibold hover:bg-gray-200">Cancel</button>
                    <button wire:click="confirmApprove" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm font-bold hover:bg-emerald-700">Sign &amp; Approve</button>
                </div>
            </div>
        </div>
    @endif
</div>
