@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Candidacy Details') }}
        </h2>
        <a href="{{ route('admin.candidacy.index') }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-1"></i> Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            
            {{-- Header --}}
            <div class="p-6 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h4 class="text-lg font-semibold text-gray-900">Candidacy Status</h4>
                    <div>
                        @if($candidacy->status == 'pending')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                        @elseif($candidacy->status == 'approved')
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Approved</span>
                        @else
                            <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Details --}}
            <div class="p-6 space-y-6">
                <h3 class="text-lg font-semibold text-gray-900">Application Details:</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Name</h4>
                        <p class="text-lg font-lg text-gray-900">{{ ucwords(str_replace('_', ' ', $candidacy->student->last_name ?? '')) }}, {{ ucwords(str_replace('_', ' ', $candidacy->student->first_name ?? '')) }} {{ ucwords(str_replace('_', ' ', $candidacy->student->middle_name ?? '')) }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Email</h4>
                        <p class="text-lg font-lg text-gray-900">{{ $candidacy->student->user->email ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Date of birth</h4>
                        <p class="text-lg font-lg text-gray-900">{{ $candidacy->student->date_of_birth ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Position Applied</h4>
                        <p class="text-lg font-lg text-gray-900">{{ ucwords(str_replace('_', ' ', $candidacy->position_applied)) }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Partylist</h4>
                        <p class="text-lg font-lg text-gray-900">
                            @if($candidacy->is_independent)
                                <span class="italic text-gray-500">Independent</span>
                            @else
                                {{ $candidacy->partylist ?? 'N/A' }}
                            @endif
                        </p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Academic Year</h4>
                        <p class="text-lg text-gray-900">{{ $candidacy->academic_year ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Submitted On</h4>
                        <p class="text-lg text-gray-900">{{ $candidacy->submitted_at ? $candidacy->submitted_at->format('F d, Y h:i A') : $candidacy->created_at->format('F d, Y h:i A') }}</p>
                    </div>
                    @if($candidacy->reviewed_at)
                    <div>
                        <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Reviewed On</h4>
                        <p class="text-lg text-gray-900">{{ $candidacy->reviewed_at->format('F d, Y h:i A') }}</p>
                    </div>
                    @endif
                </div>

                @if($candidacy->remarks)
                <div class="border-t border-gray-200 pt-6">
                    <h4 class="text-sm font-medium text-gray-500 uppercase mb-2">Remarks</h4>
                    <p class="text-gray-900 bg-gray-50 p-4 rounded-lg">{{ $candidacy->remarks }}</p>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            @if($candidacy->status == 'pending')
            <div class="p-6 bg-gray-50 border-t border-gray-200">
                <div class="flex justify-end gap-3">
                    <form action="{{ route('admin.candidacy.approve', $candidacy) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"
                            onclick="return confirm('Are you sure you want to approve this application?')">
                            <i class="fas fa-check mr-2"></i> Approve
                        </button>
                    </form>
                    <button type="button" onclick="openRejectModal({{ $candidacy->id }})" 
                        class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        <i class="fas fa-times mr-2"></i> Reject
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Application</h3>
            <form id="rejectForm" action="{{ route('admin.candidacy.reject', $candidacy) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="mb-4">
                    <label for="remarks" class="block text-sm font-medium text-gray-700 mb-2">Reason for Rejection</label>
                    <textarea name="remarks" id="remarks" rows="3" required
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500"
                        placeholder="Please provide a reason..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeRejectModal()" 
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
