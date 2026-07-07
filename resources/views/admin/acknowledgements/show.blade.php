@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto py-6" x-data="{ activeAdvisory: null }">
    
    {{-- Header Section --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
        <div>
            {{-- Dynamically shows the targeted advisory number in the header if items exist --}}
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">
                Advisory #{{ $advisory_no ?? ($acknowledgements->first()->advisory_no ?? '') }} Acknowledgements
            </h1>
            <p class="text-sm text-gray-500 mt-1">See which employees have acknowledged this specific memorandum.</p>
        </div>
    </div>
        
    {{-- Table Container --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/70 border-b border-gray-200 text-xs font-bold text-gray-600 uppercase tracking-wider">
                        <th class="py-4 px-6">Employee ID</th>
                        <th class="py-4 px-6">Employee Name</th>
                        <th class="py-4 px-6">Date Acknowledged</th>
                        <th class="py-4 px-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 text-sm text-gray-700">
                    @forelse($acknowledgements as $ack)
                        <tr class="hover:bg-gray-50/50 transition">
                            {{-- employee_id column --}}
                            <td class="py-4 px-6 font-mono font-bold text-blue-600">
                                {{ $ack->employee_id }}
                            </td>
                            
                            {{-- Employee Name via relationship safely --}}
                            <td class="py-4 px-6">
                                <div class="font-semibold text-gray-900">
                                    {{ $ack->employee->first_name ?? '' }} {{ $ack->employee->last_name ?? '' }}
                               </div>
                            </td>
                            
                            {{-- acknowledged_at column --}}
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
                                <span class="text-sm">Nobody has acknowledged this advisory yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection