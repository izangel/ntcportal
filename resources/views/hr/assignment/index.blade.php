@extends('admin.admin')

@section('content')
<div class="bg-gray-50 min-h-screen antialiased">
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <h1 class="text-xl font-bold text-gray-900">Peer Evaluation Management</h1>
            <p class="text-sm text-gray-500 mt-1">Assign and manage peer-to-peer feedback links for the current academic term.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 text-sm rounded-md flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-12 gap-8">
            
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden sticky top-8">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">New Assignment</h2>
                    </div>
                    
                    <form action="{{ route('hr.peer-assignments.store') }}" method="POST" class="p-6 space-y-5">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Target Employee</label>
                            <select name="teacher_id" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select individual to be rated</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }} {{ ($emp->mid_name && strtoupper($emp->mid_name) !== 'N/A') ? $emp->mid_name : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Peer Evaluators</label>
                            <select name="peer_ids[]" multiple required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500 min-h-[160px]">
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }} {{ ($emp->mid_name && strtoupper($emp->mid_name) !== 'N/A') ? $emp->mid_name : '' }}</option>
                                @endforeach
                            </select>
                            <p class="mt-2 text-xs text-gray-400">Hold Ctrl (Windows) or Cmd (Mac) for multiple selection.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Year</label>
                                <select name="academic_year_id" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500">
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Semester</label>
                                <select name="semester" required class="block w-full border-gray-300 rounded-lg text-sm shadow-sm focus:ring-blue-500">
                                    <option value="1st">1st Sem</option>
                                    <option value="2nd">2nd Sem</option>
                                    <option value="Summer">Summer</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2.5 bg-blue-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-800 transition ease-in-out duration-150">
                            Save Assignment
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-8">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-white">
                        <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider">Active Peer Links</h2>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                            {{ $assignments->total() }} Assignments
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Target Employee</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Evaluator (Peer)</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="relative px-6 py-3"><span class="sr-only">Delete</span></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($assignments as $assign)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <div class="font-bold text-gray-900">{{ $assign->teacher->last_name }}, {{ $assign->teacher->first_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $assign->semester }} | {{ $assign->academicYear->start_year }}-{{ $assign->academicYear->end_year }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        {{ $assign->peer->last_name }}, {{ $assign->peer->first_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($assign->is_completed)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-blue-100 text-blue-800">
                                                In Progress
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <form action="{{ route('hr.peer-assignments.destroy', $assign) }}" method="POST" onsubmit="return confirm('Permanently remove this link?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-400 hover:text-red-600 transition-colors">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    @if($assignments->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 bg-white">
                            {{ $assignments->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection