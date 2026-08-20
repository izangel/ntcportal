@extends('layouts.admin')

@section('content')
<div class="max-w-6xl mx-auto py-8 px-4">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Manage User Accounts</h1>
            <p class="text-sm text-gray-500">View, create, and clean up login accounts across the portal.</p>
        </div>

        <form action="{{ route('admin.user-accounts.index') }}" method="GET" class="mt-4 md:mt-0 flex gap-2">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="Search name or email..."
                   class="w-64 rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
            <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-xl text-sm hover:bg-black transition">
                Search
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl flex items-center text-sm">
            <svg class="w-5 h-5 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {!! session('success') !!}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center text-sm">
            <svg class="w-5 h-5 mr-2 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {!! session('error') !!}
        </div>
    @endif

    {{-- TABS --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="{{ route('admin.user-accounts.index', ['tab' => 'all', 'search' => request('search')]) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'all' ? 'bg-gray-800 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            All Accounts ({{ $counts['all'] }})
        </a>
        <a href="{{ route('admin.user-accounts.index', ['tab' => 'orphans', 'search' => request('search')]) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'orphans' ? 'bg-amber-500 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Unlinked Accounts ({{ $counts['orphans'] }})
        </a>
        <a href="{{ route('admin.user-accounts.index', ['tab' => 'students-without-account', 'search' => request('search')]) }}"
           class="px-4 py-2 rounded-xl text-sm font-semibold transition {{ $tab === 'students-without-account' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
            Students Without Accounts ({{ $counts['students-without-account'] }})
        </a>
    </div>

    {{-- TAB: ALL / ORPHANS (user rows) --}}
    @if($tab === 'all' || $tab === 'orphans')
        @php
            $rows = $tab === 'orphans' ? $orphans : $users;
        @endphp
        <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800">
                    {{ $tab === 'orphans' ? 'Unlinked User Accounts' : 'All User Accounts' }}
                </h2>
                <p class="text-xs text-gray-500">
                    {{ $tab === 'orphans' ? 'Accounts with no employee or student profile. Safe to delete.' : 'Accounts that exist in the portal, and what they are linked to.' }}
                </p>
            </div>
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Role</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Linked To</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($rows as $user)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $user->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $user->role ?: 'none' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($user->employee)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        Employee · {{ $user->employee->first_name }} {{ $user->employee->last_name }}
                                    </span>
                                @elseif($user->student)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100">
                                        Student · {{ $user->student->last_name }}, {{ $user->student->first_name }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Unlinked
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($user->employee === null && $user->student === null)
                                    <form action="{{ route('admin.user-accounts.destroy', $user->id) }}" method="POST"
                                          onsubmit="return confirm('Delete the unlinked account {{ $user->email }}? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-bold text-red-600 hover:text-red-800 uppercase tracking-wider bg-red-50 px-4 py-2 rounded-lg border border-red-100 hover:bg-red-100 transition">
                                            Delete
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Protected</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">No user accounts found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($rows->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $rows->links() }}
            </div>
            @endif
        </div>
    @endif

    {{-- TAB: STUDENTS WITHOUT ACCOUNTS --}}
    @if($tab === 'students-without-account')
        <div class="bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-sm font-bold text-gray-800">Students Without User Accounts</h2>
                <p class="text-xs text-gray-500">Students who can't log in yet. Create an account (default password: <code>northlink</code>).</p>
            </div>
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Student ID</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Name</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Email</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase">Program · Section</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($studentsWithoutAccount as $student)
                        @php $latest = $student->sections->first(); @endphp
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 text-sm font-mono font-semibold text-gray-700">{{ $student->student_id ?? $student->id }}</td>
                            <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $student->last_name }}, {{ $student->first_name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $student->email ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $latest && $latest->program ? $latest->program->name . ' » ' . $latest->name : '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.user-accounts.create-student', $student->id) }}" method="POST"
                                      onsubmit="return confirm('Create a login account for {{ $student->first_name }} {{ $student->last_name }}?');">
                                    @csrf
                                    <button type="submit" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition">
                                        Create Account
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">All students already have user accounts.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if($studentsWithoutAccount->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-100">
                {{ $studentsWithoutAccount->links() }}
            </div>
            @endif
        </div>
    @endif
</div>
@endsection
