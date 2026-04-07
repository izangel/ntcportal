@extends('layouts.admin')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4">
        
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900">Supervisor Mapping</h1>
            <p class="text-sm text-gray-500 font-medium">Link Department Heads and Deans to their respective faculty members.</p>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm sticky top-8">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6">Create Link</h3>
                    
                    <form action="{{ route('hr.supervisor-assignments.store') }}" method="POST" class="space-y-5">
                        @csrf
                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Subordinate (Faculty)</label>
                            <select name="teacher_id" required class="w-full border-gray-200 rounded-xl text-sm focus:ring-indigo-500">
                                <option value="">Select Employee</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Assign Supervisor (Head/Dean)</label>
                            <select name="supervisor_id" required class="w-full border-gray-200 rounded-xl text-sm focus:ring-indigo-500">
                                <option value="">Select Supervisor</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->last_name }}, {{ $emp->first_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Year</label>
                                <select name="academic_year_id" class="w-full border-gray-200 rounded-xl text-sm">
                                    @foreach($academicYears as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->start_year }}-{{ $ay->end_year }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-black text-gray-500 uppercase mb-2">Term</label>
                                <select name="semester" class="w-full border-gray-200 rounded-xl text-sm">
                                    <option value="1st">1st Sem</option>
                                    <option value="2nd">2nd Sem</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-gray-900 text-white font-bold py-4 rounded-xl text-xs uppercase tracking-widest hover:bg-indigo-600 transition-all shadow-lg">
                            Establish Link
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-span-12 lg:col-span-8">
                <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr class="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="px-6 py-4">Subordinate</th>
                                <th class="px-6 py-4">Supervisor</th>
                                <th class="px-6 py-4">Period</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($assignments as $assign)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <h3 class="text-base font-semibold text-gray-900">{{ $assign->teacher->last_name }}, {{ $assign->teacher->first_name }}</h3>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="whitespace-nowrap text-sm text-gray-600">{{ $assign->peer->last_name }}, {{ $assign->peer->first_name }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="whitespace-nowrap text-sm text-gray-600">{{ $assign->semester }} | {{ $assign->academicYear->start_year }}-{{ $assign->academicYear->end_year }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="{{ $assign->is_completed ? 'text-green-600 bg-green-100 text-[10px] font-black uppercase border border-green-100' : 'bg-gray-200 text-gray-700 text-[10px] font-black uppercase border gray-yellow-100' }} px-2 py-1 rounded">
                                        {{ $assign->is_completed ? 'Completed' : 'Pending' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <form action="{{ route('hr.supervisor-assignments.destroy', $assign) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button class="text-gray-300 hover:text-red-500 transition-colors">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection