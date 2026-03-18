@extends('admin.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Candidacy Requirements') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            <div class="mb-8">
                <h3 class="text-2xl font-bold text-gray-900">Graduation Candidacy Requirements</h3>
                <p class="mt-2 text-gray-600">Review all the requirements needed to apply for graduation candidacy.</p>
            </div>

            {{-- Academic Requirements --}}
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-graduation-cap text-blue-600 mr-2"></i>
                    Academic Requirements
                </h4>
                <div class="bg-gray-50 rounded-lg p-6">
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-800">Filled-out Certificate of Candidacy Form</p>
                                <p class="text-sm text-gray-500">Please provide the fully filled-out COC form.</p>
                            </div>
                        </li>
                        
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mt-1 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-800">Photocopy of Student ID</p>
                                <p class="text-sm text-gray-500">Ensure the ID is clear and currently valid.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Important Notes --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <h4 class="text-lg font-semibold text-yellow-800 mb-2 flex items-center">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Important Notes
                </h4>
                <ul class="text-sm text-yellow-700 space-y-2">
                    <li>• Applications must be submitted by <span class="text-blue-800 font-bold">March 12, 2026</span>, prior to the graduation ceremony.</li>
                    <li>• Incomplete applications will not be processed. Please ensure all requirements are met.</li>
                    <li>• Declaration of Qualified Candidates <span class="text-blue-800 font-bold">March 13, 2026</span>.</li>
                </ul>
            </div>

            {{-- Action Button --}}
            <div class="text-center">
                <a href="{{ route('student.candidacy.index') }}" 
                    class="inline-block px-8 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                    <i class="fas fa-file-alt mr-2"></i>
                    Apply for Candidacy
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
