@extends('layouts.admin')

@section('header')
    <div class="flex items-center justify-between">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Certificate of Candidacy') }}
        </h2>
        <span class="text-sm text-gray-500 font-medium">{{ now()->format('l, F j, Y') }}</span>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">

            @if($existingCandidacy)
                {{-- Already Submitted Message --}}
                <div class="text-center py-12">
                    <div class="mb-6">
                        <div class="w-20 h-20 mx-auto rounded-full bg-green-100 flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-600 text-4xl"></i>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Application Already Submitted</h3>
                    <p class="text-gray-600 mb-6">You have already submitted your candidacy application.</p>
                    
                    <div class="bg-gray-50 rounded-lg p-6 max-w-md mx-auto mb-6">
                        <div class="text-left space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Position:</span>
                                <span class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $existingCandidacy->position_applied)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Partylist:</span>
                                <span class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $existingCandidacy->partylist)) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status:</span>
                                <span class="font-medium {{ $existingCandidacy->status == 'pending' ? 'text-yellow-600' : ($existingCandidacy->status == 'approved' ? 'text-green-600' : 'text-red-600') }}">
                                    {{ ucfirst($existingCandidacy->status) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Submitted:</span>
                                <span class="font-medium text-gray-900">{{ $existingCandidacy->submitted_at ? $existingCandidacy->submitted_at->format('M d, Y') : $existingCandidacy->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('student.candidacy.status') }}" 
                        class="inline-block px-6 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-medium">
                        <i class="fas fa-eye mr-2"></i> View Application Status
                    </a>
                </div>
            @else
                {{-- Disclaimer Section --}}
                <div class="border border-gray-300 p-4 mb-6">
                    <p class="font-semibold text-gray-800 mb-2">DISCLAIMER :</p>
                    <p class="font-bold text-gray-900 underline mb-3">Statement of Approval/Consent per RA 10173 or the Data Privacy Act (DPA) of 2012</p>
                    <p class="text-sm text-gray-700 leading-relaxed">
                        Please be informed that as participant of this Supreme Student Government campaign and election, you agree and consent the Office of the Student Affairs to collect, use, and disclose personal information such as full name/s, address/es, contact details, including pictures which shall be used by the office for reporting and documentation purposes only.
                    </p>
                </div>

                <form action="{{ route('student.candidacy.store') }}" method="POST" class="space-y-6">
                    @csrf

                    {{-- I. Personal Information Section --}}
                    <div class="mb-6">
                        <h4 class="text-base font-semibold text-gray-800 mb-4">I. &nbsp;&nbsp;&nbsp;&nbsp; Personal Information</h4>
                        
                        <div class="border border-gray-300">
                            {{-- Name of Candidate Row (Display Only) --}}
                            <div class="border-b border-gray-300 p-4">
                                <div class="grid grid-cols-12 gap-4 items-end">
                                    <div class="col-span-12 md:col-span-5">
                                        <label class="block text-sm text-gray-700 mb-1">Name of Candidate :</label>
                                        <p class="w-full border-b border-gray-400 py-1 text-center text-gray-900">{{ Auth::user()->student->last_name ?? '' }}</p>
                                        <p class="text-xs text-gray-500 text-center mt-1">Last Name</p>
                                    </div>
                                    <div class="col-span-12 md:col-span-5">
                                        <p class="w-full border-b border-gray-400 py-1 text-center text-gray-900">{{ Auth::user()->student->first_name ?? '' }}</p>
                                        <p class="text-xs text-gray-500 text-center mt-1">First Name</p>
                                    </div>
                                    <div class="col-span-12 md:col-span-2">
                                        <p class="w-full border-b border-gray-400 py-1 text-center text-gray-900">{{ Auth::user()->student->middle_name ?? '' }}</p>
                                        <p class="text-xs text-gray-500 text-center mt-1">MI</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border-b border-gray-300 p-4">
                                <div class="grid grid-cols-12 gap-4 items-end">
                                    <div class="col-span-12 md:col-span-5">
                                        <p class="w-full border-b border-gray-400 py-1 text-center text-gray-900">{{ Auth::user()->student->date_of_birth ?? '' }}</p>
                                        <p class="text-xs text-gray-500 text-center mt-1">Date of birth</p>
                                    </div>
                                    <div class="col-span-12 md:col-span-5">
                                        <p class="w-full border-b border-gray-400 py-1 text-center text-gray-900">{{ Auth::user()->student->email ?? '' }}</p>
                                        <p class="text-xs text-gray-500 text-center mt-1">Email</p>
                                    </div>
                                    <div class="col-span-12 md:col-span-2">
                                        <p class="w-full border-b border-gray-400 py-1 text-center text-gray-900">{{ $activeAcademicYear ? $activeAcademicYear->start_year . '-' . $activeAcademicYear->end_year : '' }}</p>
                                        <p class="text-xs text-gray-500 text-center mt-1">Academic Year</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Position & Partylist Section --}}
                            <div class="p-4 space-y-6">
                                <div class="flex items-end gap-2">
                                    <label class="text-sm font-medium text-gray-700 w-24">Position :</label>
                                    <div class="flex-1">
                                        <select name="position" id="position" class="w-full border-0 border-b border-gray-400 bg-transparent focus:ring-0 focus:border-blue-500 px-0 text-sm" required>
                                            <option value="" disabled selected>Select Position</option>
                                            <option value="president">President</option>
                                            <option value="vice_president">Vice President</option>
                                            <option value="secretary">Secretary</option>
                                            <option value="treasurer">Treasurer</option>
                                            <option value="auditor">Auditor</option>
                                            <option value="pio">PIO</option>
                                            <option value="business_manager">Business Manager</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex items-end gap-2 flex-1">
                                        <label class="text-sm font-medium text-gray-700 w-24">Partylist :</label>
                                        <div class="flex-1">
                                            <input type="text" name="partylist" id="partylist" placeholder="Name of Partylist" 
                                                class="w-full border-0 border-b border-gray-400 bg-transparent focus:ring-0 focus:border-blue-500 px-0 text-sm disabled:opacity-50"
                                                oninput="document.getElementById('is_independent').checked = false">
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 pt-2">
                                        <input type="checkbox" name="is_independent" id="is_independent" value="1" 
                                            class="rounded border-gray-400 text-blue-600 focus:ring-blue-500"
                                            onclick="if(this.checked) document.getElementById('partylist').value = ''">
                                        <label for="is_independent" class="text-sm text-gray-600 cursor-pointer italic">Independent</label>
                                    </div>
                                </div>
                                <div class="bg-blue-50 border-t border-gray-300 p-4">
                                    <div class="flex items-start gap-3">
                                        <i class="fab fa-google-drive text-blue-600 text-xl mt-1"></i>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-800">Requirement: Student ID Upload</label>
                                            <p class="text-sm text-gray-600 mb-2">Please upload a scanned copy of your Student ID to our official Google Drive folder before submitting.</p>
                                            <a href="https://drive.google.com/drive/folders/1ll0nBJvq1a4I1rxezkaNCQO5VWSxI5_F" target="_blank" 
                                            class="inline-flex items-center text-sm font-bold text-blue-700 hover:text-blue-800 underline decoration-2 underline-offset-4">
                                                Upload Candidate's Scanned Copy of Student ID 
                                                <i class="fas fa-external-link-alt ml-2 text-xs"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('dashboard') }}" 
                            class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition">
                            Cancel
                        </a>
                        <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Submit Application
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
