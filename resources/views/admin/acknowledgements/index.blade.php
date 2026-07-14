@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6" x-data="{ activeAdvisory: null }">

    {{-- Role Authorization Check --}}
    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('hr') || Auth::user()->hasRole('academic_head'))
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Acknowledgements</h1>
            <p class="text-sm text-gray-500 mt-1">See who acknowledged those memorandum and advisories.</p>
        </div>
    </div>

      {{-- Success Message Alert --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl shadow-sm flex items-center">
            <i class="fa-solid fa-circle-check text-emerald-500 text-xl mr-3"></i>
            <span class="text-sm font-medium text-emerald-800">{{ session('success') }}</span>
        </div>
    @endif
        
    {{-- Table Container --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <th class="py-4 px-6">Employee ID</th>
                        <th class="py-4 px-6">Full name</th>
                        <th class="py-4 px-6">Advisory No.</th>
                        <th class="py-4 px-6">Date Acknowledged</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 text-sm text-gray-700">
                    @forelse($acknowledgements as $ack)
                        <tr class="hover:bg-gray-50/50 transition">
                            {{-- employee_id --}}
                            <td class="py-4 px-6 font-mono font-bold text-blue-600">
                                {{ $ack->employee_id }}
                            </td>

                             {{-- Employee Name --}}
                        <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">
                            {{ $ack->employee->first_name ?? 'N/A' }} {{ $ack->employee->last_name ?? '' }}
                        </td>
                            
                            {{-- advisory_no --}}
                            <td class="py-4 px-6 font-semibold text-gray-900">
                                #{{ $ack->advisory_no }}
                            </td>
                            
                            {{-- acknowledged_at --}}
                            <td class="py-4 px-6 text-gray-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($ack->acknowledged_at)->format('M d, Y h:i A') }}
                            </td>
                            
                            {{-- Action Triggers --}}
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                @if(Auth::user()->hasAnyRole(['admin', 'hr', 'academic_head', 'program_head_shs']))
                                    <button 
                                        type="button"
                                        onclick="if(confirm('Are you sure you want to delete this acknowledgement?')) { document.getElementById('delete-form-{{ $ack->id }}').submit(); }"
                                        class="inline-flex items-center text-sm font-semibold text-red-600 hover:text-red-800 transition">
                                        <i class="fa-regular fa-trash-can mr-1.5"></i> Delete
                                    </button>

                                    <form id="delete-form-{{ $ack->id }}" action="{{ route('admin.acknowledgements.destroy', $ack->id) }}" method="POST" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-12 text-center text-gray-400">
                                <i class="fa-solid fa-file-invoice text-4xl mb-3 text-gray-300 block"></i>
                                <span class="text-sm">No acknowledgements have been recorded yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endsection