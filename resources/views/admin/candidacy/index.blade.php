@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Candidacy Applications') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Google Drive Link Settings --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-200 bg-blue-50">
                <div class="flex items-start gap-3">
                    <i class="fab fa-google-drive text-blue-600 text-2xl mt-1"></i>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Student ID Upload Link (Google Drive)</h3>
                        <p class="text-sm text-gray-600 mb-3">This link will be shown to students in the candidacy application form for uploading their Student ID.</p>
                        
                        <form action="{{ route('admin.candidacy.updateDriveLink') }}" method="POST" class="flex flex-col md:flex-row gap-3">
                            @csrf
                            <input type="url" name="google_drive_link" value="{{ $googleDriveLink }}" 
                                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="https://drive.google.com/drive/folders/..." required>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm whitespace-nowrap">
                                <i class="fas fa-save mr-1"></i> Update Link
                            </button>
                        </form>
                        @error('google_drive_link')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        
                        <a href="{{ $googleDriveLink }}" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 mt-2">
                            <i class="fas fa-external-link-alt mr-1"></i> Open Current Link
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Application Status Toggle --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 {{ $isApplicationOpen ? 'bg-green-50' : 'bg-red-50' }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full {{ $isApplicationOpen ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                            <i class="fas {{ $isApplicationOpen ? 'fa-door-open text-green-600' : 'fa-door-closed text-red-600' }} text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold {{ $isApplicationOpen ? 'text-green-800' : 'text-red-800' }}">
                                Application Status: {{ $isApplicationOpen ? 'OPEN' : 'CLOSED' }}
                            </h3>
                            <p class="text-sm {{ $isApplicationOpen ? 'text-green-600' : 'text-red-600' }}">
                                @if($isApplicationOpen)
                                    Students can currently submit their candidacy applications.
                                @else
                                    Students cannot submit candidacy applications at this time.
                                @endif
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('admin.candidacy.toggleApplication') }}" method="POST">
                        @csrf
                        <button type="submit" 
                            class="px-5 py-2.5 rounded-md text-white font-medium text-sm transition
                            {{ $isApplicationOpen ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700' }}"
                            onclick="return confirm('Are you sure you want to {{ $isApplicationOpen ? 'close' : 'open' }} candidacy applications?')">
                            <i class="fas {{ $isApplicationOpen ? 'fa-lock' : 'fa-unlock' }} mr-2"></i>
                            {{ $isApplicationOpen ? 'Close Applications' : 'Open Applications' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            {{-- Header & Filters --}}
            <div class="p-6 border-b border-gray-200">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <h3 class="text-lg font-semibold text-gray-900">SSG Election - Candidacy Applications</h3>
                    
                    <form action="{{ route('admin.candidacy.index') }}" method="GET" class="flex flex-col md:flex-row gap-3">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name..." 
                            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        
                        <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        </select>
                        
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                    </form>
                </div>
            </div>

            {{-- Statistics --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50 border-b border-gray-200">
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Total Applications</p>
                    <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Candidacy::count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\Candidacy::pending()->count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Approved</p>
                    <p class="text-2xl font-bold text-green-600">{{ \App\Models\Candidacy::approved()->count() }}</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow-sm">
                    <p class="text-sm text-gray-500">Rejected</p>
                    <p class="text-2xl font-bold text-red-600">{{ \App\Models\Candidacy::rejected()->count() }}</p>
                </div>
            </div>

            {{-- Records Per Position --}}
            <div class="p-6 bg-white border-b border-gray-200">
                <h4 class="text-sm font-semibold text-gray-700 mb-4">Records Per Position</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7 gap-3">
                    @foreach($positionOrder as $positionKey => $positionLabel)
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">{{ $positionLabel }}</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ $positionCounts[$positionKey] ?? 0 }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Candidate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Partylist</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($applications as $application)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-blue-600 font-medium">{{ substr($application->student->first_name ?? '', 0, 1) }}{{ substr($application->student->last_name ?? '', 0, 1) }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $application->student->last_name ?? '' }}, {{ $application->student->first_name ?? '' }} {{ $application->student->middle_name ?? '' }}</div>
                                            <div class="text-sm text-gray-500">{{ $application->student->user->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">{{ ucwords(str_replace('_', ' ', $application->position_applied)) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">
                                        @if($application->is_independent)
                                            <span class="italic text-gray-500">Independent</span>
                                        @else
                                            {{ $application->partylist ?? 'N/A' }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $application->submitted_at ? $application->submitted_at->format('M d, Y') : $application->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($application->status == 'pending')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Pending
                                        </span>
                                    @elseif($application->status == 'approved')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Approved
                                        </span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                            Rejected
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-3">
                                        {{-- View Link --}}
                                        <a href="{{ route('admin.candidacy.show', $application) }}"
                                            class="text-blue-600 hover:text-blue-900 hover:underline">
                                            View
                                        </a>

                                        {{-- Edit Link --}}
                                        <a href="{{ route('admin.candidacy.edit', $application) }}"
                                            class="text-indigo-600 hover:text-indigo-900 hover:underline">
                                            Edit
                                        </a>

                                        @if($application->status == 'pending')
                                            {{-- Approve Form --}}
                                            <form action="{{ route('admin.candidacy.approve', $application) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900 hover:underline"
                                                    onclick="return confirm('Are you sure you want to approve this application?')">
                                                    Approve
                                                </button>
                                            </form>

                                            {{-- Reject Button --}}
                                            <button type="button" class="text-red-600 hover:text-red-900 hover:underline"
                                                onclick="openRejectModal({{ $application->id }})">
                                                Reject
                                            </button>
                                        @endif

                                        {{-- Delete Button --}}
                                        <form action="{{ route('admin.candidacy.destroy', $application) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="text-red-600 hover:text-red-900 hover:underline"
                                                onclick="return confirm('WARNING: This will permanently delete the application. This action cannot be undone. Proceed?')">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4"></i>
                                    <p>No candidacy applications found.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $applications->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Application</h3>
            <form id="rejectForm" method="POST">
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
    function openRejectModal(id) {
        document.getElementById('rejectForm').action = '/admin/candidacy/' + id + '/reject';
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
</script>
@endsection
