@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Employees') }}
    </h2>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">

                @if (session('password_success'))
                    <div class="bg-amber-100 border border-amber-400 text-amber-900 px-4 py-3 rounded relative mb-4 shadow-sm" role="alert">
                        <span class="block sm:inline">{!! session('password_success') !!}</span>
                    </div>
                @endif

                {{-- Metric Cards added back right here --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-indigo-50 border border-indigo-100 p-4 rounded-lg shadow-sm">
                        <span class="text-xs font-bold text-indigo-700 uppercase block tracking-wider">Total Headcount</span>
                        <span class="text-2xl font-black text-indigo-900">{{ \App\Models\Employee::count() }}</span>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-lg shadow-sm">
                        <span class="text-xs font-bold text-emerald-700 uppercase block tracking-wider">Faculty (Teachers)</span>
                        <span class="text-2xl font-black text-emerald-900">{{ \App\Models\Employee::where('role', 'teacher')->count() }}</span>
                    </div>
                    <div class="bg-amber-50 border border-amber-100 p-4 rounded-lg shadow-sm">
                        <span class="text-xs font-bold text-amber-700 uppercase block tracking-wider">Linked Portal Accounts</span>
                        <span class="text-2xl font-black text-amber-900">{{ \App\Models\Employee::whereNotNull('user_id')->count() }}</span>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg shadow-sm">
                        <span class="text-xs font-bold text-gray-600 uppercase block tracking-wider">Archived Profiles</span>
                        <span class="text-2xl font-black text-gray-800">{{ \App\Models\Employee::onlyTrashed()->count() }}</span>
                    </div>
                </div>
                
                {{-- CALL LIVEWIRE INSTEAD OF PRINTING STATIC TABLES --}}
                @livewire('employee-index')

            </div>
        </div>
    </div>
@endsection