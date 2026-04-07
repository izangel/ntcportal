@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Student Account Management</h1>
            <p class="text-sm text-gray-500">Search students and manage login credentials.</p>
        </div>

        <form action="{{ route('admin.student-accounts.index') }}" method="GET" class="mt-4 md:mt-0 flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                   placeholder="Search ID or Email..." 
                   class="w-64 rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-xl text-sm hover:bg-black transition">
                Search
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">ID</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Email Address</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-semibold text-gray-600 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="px-6 py-4 text-sm font-medium text-gray-700">#{{ $student->id }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $student->email }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            Student Account
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <form action="{{ route('admin.student-accounts.reset', $student->id) }}" method="POST" 
                              onsubmit="return confirm('Are you sure you want to reset this password to: northlink?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition">
                                Reset Password
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">No student accounts found matching your criteria.</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($students->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection