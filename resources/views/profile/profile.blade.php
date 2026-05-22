@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Student Profile Account') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
        
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 relative flex flex-col md:flex-row items-center md:items-start justify-between gap-4">
                <div class="flex flex-col md:flex-row items-center md:items-start gap-5 text-center md:text-left z-10 w-full">
                    
                    <div class="w-32 h-32 flex-shrink-0 rounded-full border-4 border-white bg-slate-200 shadow-md overflow-hidden flex items-center justify-center">
                        @if(!empty($user->profile_photo_url))
                            <img src="{{ $user->profile_photo_url }}" alt="{{ $user->username ?? $user->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-3xl font-black text-indigo-600 uppercase">
                                {{ substr($user->username ?? $user->name ?? 'ST', 0, 2) }}
                            </span>
                        @endif
                    </div>
                    
                    <div class="flex flex-col justify-start w-full">
                        <h3 class="text-2xl font-extrabold text-gray-900 mt-2">
                            {{ $user->username ?? $user->name ?? 'Christian Jay Orcullo' }}
                        </h3>
                        
                        <div class="flex flex-wrap items-center gap-3 mt-4 justify-center md:justify-start">
                            <a href="{{ route('profile.personal-information.edit') }}" class="px-5 py-2 bg-amber-400 hover:bg-amber-500 text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition-all duration-150 flex items-center gap-2">
                                <i class="fas fa-user-edit"></i> Edit Profile
                            </a>
                            <button onclick="window.print()" class="px-5 py-2 bg-slate-600 hover:bg-slate-700 text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition-all duration-150 flex items-center gap-2">
                                <i class="fas fa-print"></i> Print Profile
                            </button>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <h4 class="font-bold text-gray-800 text-xs uppercase tracking-wider mb-5 pb-2 border-b border-gray-50 flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-500"></i> Primary Account Information
                    </h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Full Registered Name</label>
                            <p class="text-sm font-semibold text-gray-900 bg-gray-50 p-3 rounded-xl mt-1.5 border border-gray-100">
                                {{ $user->username ?? $user->name ?? '' }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">System Email Address</label>
                            <p class="text-sm font-semibold text-gray-900 bg-gray-50 p-3 rounded-xl mt-1.5 border border-gray-100">
                                {{ $user->email ?? '' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection