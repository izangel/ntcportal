<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Evaluation Workflow Control') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6 px-4 sm:px-0 flex justify-between items-end">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Setup Progress</h3>
                    <p class="mt-1 text-sm text-gray-600">Follow these steps to prepare the system for teacher evaluations.</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Current Status</span>
                    <p class="text-sm font-bold {{ optional($currentCycle)->evaluations_opened ? 'text-red-600' : 'text-blue-600' }}">
                        {{ optional($currentCycle)->evaluations_opened ? 'LIVE / OPEN' : 'PRE-EVALUATION PHASE' }}
                    </p>
                </div>
            </div>

            <div class="mb-6">
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 shadow-sm rounded-r-lg" role="alert">
                        <p class="font-bold">Success</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                @if (session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 shadow-sm rounded-r-lg" role="alert">
                        <p class="font-bold">Error</p>
                        <p>{{ session('error') }}</p>
                    </div>
                @endif
            </div>

            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-8">
                <div class="relative">
                    <div class="absolute left-8 top-0 h-full w-0.5 bg-gray-200"></div>

                    <div class="relative mb-12 flex items-start space-x-6">
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-full {{ optional($currentCycle)->period_verified ? 'bg-green-100' : 'bg-blue-100' }} ring-8 ring-white z-10">
                            <span class="text-xl font-bold {{ optional($currentCycle)->period_verified ? 'text-green-600' : 'text-blue-600' }}">1</span>
                        </div>

                        <div class="min-w-0 flex-1 py-1.5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 leading-none">Current Academic Period</h4>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">Responsible Person: Registrar</span>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    @if(!optional($currentCycle)->period_verified && Auth::user()->hasRole('registrar'))
                                        <a href="{{ route('semesters.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Go to Academic Settings →</a>
                                        <form action="{{ route('evaluation.verifyPeriod') }}" method="POST">
                                            @csrf
                                            <x-button class="bg-blue-600 hover:bg-blue-700 text-[10px]">Verify Period</x-button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4">
                                <div class="flex items-center space-x-2">
                                    <span class="text-2xl font-black text-gray-800">{{ $context['ay']->start_year }}-{{ $context['ay']->end_year }}</span>
                                    <span class="text-gray-400">|</span>
                                    <span class="text-lg font-medium text-gray-600">{{ $context['semester'] }}</span>
                                </div>
                                @if($currentCycle && $currentCycle->period_verified)
                                    <p class="mt-1 text-xs text-green-600 font-bold flex items-center italic">✓ Period Confirmed</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative mb-12 flex items-start space-x-6 {{ !optional($currentCycle)->period_verified ? 'opacity-50' : '' }}">
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-full {{ optional($currentCycle)->blocks_verified ? 'bg-green-100' : 'bg-gray-100' }} ring-8 ring-white z-10">
                            <span class="text-xl font-bold {{ optional($currentCycle)->blocks_verified ? 'text-green-600' : 'text-gray-500' }}">2</span>
                        </div>
                        
                        <div class="min-w-0 flex-1 py-1.5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 leading-none">Block Verification</h4>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">Responsible Person: Program Heads</span>
                                </div>
                                @if(optional($currentCycle)->period_verified && !optional($currentCycle)->blocks_verified && (Auth::user()->hasRole('program_head_shs') || Auth::user()->hasRole('program_head_college')))
                                    <a href="{{ route('course_blocks.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Manage Course Blocks →</a>
                                @endif
                            </div>

                            <div class="mt-4">
                                @if(optional($currentCycle)->period_verified)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-w-md space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-gray-500 uppercase">SHS Department</span>
                                            @if(optional($currentCycle)->shs_blocks_verified)
                                                <span class="text-green-600 font-bold text-xs">✓ Verified</span>
                                            @elseif(auth()->user()->hasRole('program_head_shs'))
                                                <form action="{{ route('evaluation.verifyBlocks') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="type" value="shs">
                                                    <button type="submit" class="text-[10px] bg-white border border-gray-300 px-2 py-1 rounded shadow-sm hover:bg-gray-50">Verify SHS</button>
                                                </form>
                                            @endif
                                        </div>
                                        <hr class="border-gray-100">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-gray-500 uppercase">College Department</span>
                                            @if(optional($currentCycle)->college_blocks_verified)
                                                <span class="text-green-600 font-bold text-xs">✓ Verified</span>
                                            @elseif(auth()->user()->hasRole('program_head_college'))
                                                <form action="{{ route('evaluation.verifyBlocks') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="type" value="college">
                                                    <button type="submit" class="text-[10px] bg-white border border-gray-300 px-2 py-1 rounded shadow-sm hover:bg-gray-50">Verify College</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @else
                                    <p class="text-xs italic text-gray-400">Locked</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative mb-12 flex items-start space-x-6 {{ !optional($currentCycle)->blocks_verified ? 'opacity-50' : '' }}">
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-full {{ optional($currentCycle)->students_verified ? 'bg-green-100' : 'bg-gray-100' }} ring-8 ring-white z-10">
                            <span class="text-xl font-bold {{ optional($currentCycle)->students_verified ? 'text-green-600' : 'text-gray-500' }}">3</span>
                        </div>

                        <div class="min-w-0 flex-1 py-1.5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 leading-none">Student List Confirmation</h4>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">Responsible Person: Registrar</span>
                                </div>
                                <div class="flex flex-col items-end space-y-2">
                                    @if(optional($currentCycle)->blocks_verified && !optional($currentCycle)->students_verified && Auth::user()->hasRole('registrar'))
                                        <a href="{{ route('students.index') }}" class="text-xs font-bold text-blue-600 hover:underline">Manage Student List →</a>
                                        <form action="{{ route('evaluation.verifyStudents') }}" method="POST">
                                            @csrf
                                            <x-button class="bg-indigo-600 hover:bg-indigo-700 text-[10px]">Finalize List</x-button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-4 text-xs">
                                @if(optional($currentCycle)->students_verified)
                                    <p class="text-green-600 font-bold italic">✓ All students encoded and finalized.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative mb-12 flex items-start space-x-6 {{ !optional($currentCycle)->students_verified ? 'opacity-50' : '' }}">
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-full {{ optional($currentCycle)->loading_verified ? 'bg-green-100' : 'bg-gray-100' }} ring-8 ring-white z-10">
                            <span class="text-xl font-bold {{ optional($currentCycle)->loading_verified ? 'text-green-600' : 'text-gray-500' }}">4</span>
                        </div>

                        <div class="min-w-0 flex-1 py-1.5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 leading-none">Subject Loading Verification</h4>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">Responsible Person: Registrar & SHS Head</span>
                                </div>
                                @if(optional($currentCycle)->students_verified && !optional($currentCycle)->loading_verified && (Auth::user()->hasRole('registrar') || Auth::user()->hasRole('program_head_shs')))
                                    <a href="{{ route('assign.courseblocks') }}" class="text-xs font-bold text-blue-600 hover:underline">View Students Subject Loading →</a>
                                @endif
                            </div>

                            <div class="mt-4">
                                @if(optional($currentCycle)->students_verified)
                                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 max-w-md space-y-4">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-gray-500 uppercase">SHS Loading (SHS Head)</span>
                                            @if(optional($currentCycle)->shs_loading_verified)
                                                <span class="text-green-600 font-bold text-xs">✓ Verified</span>
                                            @elseif(auth()->user()->hasRole('program_head_shs'))
                                                <form action="{{ route('evaluation.verifyLoading') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="type" value="shs">
                                                    <button type="submit" class="text-[10px] bg-white border border-gray-300 px-2 py-1 rounded shadow-sm hover:bg-gray-50">Verify SHS</button>
                                                </form>
                                            @endif
                                        </div>
                                        <hr class="border-gray-100">
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-gray-500 uppercase">College Loading (Registrar)</span>
                                            @if(optional($currentCycle)->college_loading_verified)
                                                <span class="text-green-600 font-bold text-xs">✓ Verified</span>
                                            @elseif(auth()->user()->hasRole('registrar'))
                                                <form action="{{ route('evaluation.verifyLoading') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="type" value="college">
                                                    <button type="submit" class="text-[10px] bg-white border border-gray-300 px-2 py-1 rounded shadow-sm hover:bg-gray-50">Verify College</button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="relative flex items-start space-x-6 {{ !optional($currentCycle)->loading_verified ? 'opacity-50' : '' }}">
                        <div class="relative flex h-16 w-16 items-center justify-center rounded-full {{ optional($currentCycle)->evaluations_opened ? 'bg-red-600 text-white' : 'bg-gray-100 text-gray-400' }} ring-8 ring-white z-10">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </div>
                        <div class="min-w-0 flex-1 py-1.5">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900 leading-none">Evaluation Phase</h4>
                                    <span class="inline-block mt-2 px-2 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-600 uppercase">Responsible Person: Guidance / Academic Head</span>
                                </div>
                                @if(optional($currentCycle)->loading_verified && !optional($currentCycle)->evaluations_opened && (Auth::user()->hasRole('guidance') || Auth::user()->hasRole('academic_head')))
                                    <form action="{{ route('evaluation.openEvaluations') }}" method="POST">
                                        @csrf
                                        <x-danger-button type="submit" class="bg-red-600 animate-pulse text-[10px]">Open Evaluations</x-danger-button>
                                    </form>
                                @endif
                            </div>

                            <p class="mt-4 text-xs font-medium">
                                @if(optional($currentCycle)->evaluations_opened)
                                    <span class="text-red-600 font-bold uppercase tracking-widest animate-pulse">● Evaluation is Live</span>
                                @elseif(optional($currentCycle)->loading_verified)
                                    <span class="text-blue-600 italic">Ready for launch. All preparations verified.</span>
                                @else
                                    <span class="text-gray-400 italic">Waiting for all departmental verifications.</span>
                                @endif
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>