@extends('layouts.admin')

@section('header')
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('Student Profile Data Bank') }}
    </h2>
@endsection

@section('content')
<div class="py-12" x-data="{ tab: '{{ request('tab', 'personal') }}' }">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-6">

            <div class="w-full md:w-1/4 space-y-6">

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-center border">
                    <div class="relative inline-block">
                        <div class="relative block w-32 h-32 mx-auto mb-4 rounded-full overflow-hidden border-4 border-indigo-500 shadow-sm">
                            <img class="h-full w-full object-cover"
                                 src="{{ $student->profile_photo ? asset('storage/' . $student->profile_photo) : asset('images/ntc-logo.jpeg') }}"
                                 alt="Profile">
                        </div>
                    </div>
                    <h3 class="text-md font-bold text-gray-900 leading-tight">{{ $student->first_name }} {{ $student->last_name }}</h3>
                    <p class="text-xs font-semibold text-indigo-600 mb-4">ID: {{ $student->student_id }}</p>

                    <div class="border-t pt-4">
                        <a href="{{ route('students.edit', $student) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-[10px] text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Edit Profile
                        </a>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-t-4 border-indigo-500">
                    <div class="p-4 border-b">
                        <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wider">Search Students</h3>
                    </div>

                    <div class="p-4">
                        <form method="GET" action="{{ route('students.show', $student->id) }}" class="mb-3">
                            <div class="flex">
                                <input type="text" name="search_table" placeholder="Name or ID..."
                                       value="{{ request('search_table') }}"
                                       class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-l-md text-[11px] py-1 px-2">
                                <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded-r-md hover:bg-indigo-700 font-bold text-[11px]">
                                    GO
                                </button>
                            </div>
                        </form>

                        <div class="overflow-hidden border rounded shadow-sm">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($studentList as $item)
                                        <tr class="hover:bg-indigo-50 transition cursor-pointer {{ $item->id == $student->id ? 'bg-indigo-50' : '' }}"
                                            onclick="window.location='{{ route('students.show', $item->id) }}'">
                                            <td class="px-3 py-2">
                                                <div class="font-bold text-indigo-700 text-xs leading-none">{{ $item->last_name }}, {{ $item->first_name }}</div>
                                                <div class="text-[9px] text-gray-500 font-mono mt-1">{{ $item->student_id }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-3 py-4 text-center text-gray-400 text-[10px] italic">No records found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-[10px]">
                            {{ $studentList->appends(['search_table' => request('search_table')])->links('pagination::simple-tailwind') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-3/4">
                <div class="flex space-x-1 mb-4 bg-white p-1 shadow-sm rounded-lg border">
                    <button @click="tab = 'personal'" :class="tab === 'personal' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="flex-1 px-3 py-2 rounded-md text-xs font-bold transition">
                        PERSONAL DATA
                    </button>
                    <button @click="tab = 'education'" :class="tab === 'education' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="flex-1 px-3 py-2 rounded-md text-xs font-bold transition">
                        EDUCATION
                    </button>
                    <button @click="tab = 'documents'" :class="tab === 'documents' ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100'" class="flex-1 px-3 py-2 rounded-md text-xs font-bold transition">
                        DOCUMENTS
                    </button>
                </div>

                <div x-show="tab === 'personal'" class="bg-white shadow-sm sm:rounded-lg p-6 border">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-2 border-b pb-2"><h4 class="font-bold text-indigo-700 uppercase text-[10px]">Student Personal Data</h4></div>
                        <p class="text-sm"><strong>First Name:</strong> {{ $student->first_name }}</p>
                        <p class="text-sm"><strong>Middle Name:</strong> {{ $student->middle_name ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Last Name:</strong> {{ $student->last_name }}</p>
                        <p class="text-sm"><strong>Birthdate:</strong> {{ $student->date_of_birth ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Gender:</strong> {{ $student->gender ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Civil Status:</strong> {{ $student->civil_status ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Nationality:</strong> {{ $student->nationality ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Email:</strong> {{ $student->email }}</p>

                        <p class="text-sm"><strong>Mobile Number:</strong> {{ $student->mobile_number ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Religion:</strong> {{ $student->religion ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Place of Birth:</strong> {{ $student->place_birth ?? 'N/A' }}</p>
                        <p class="text-sm"><strong>Card Number:</strong> {{ $student->card_number ?? 'N/A' }}</p>
                        <p class="text-sm col-span-2"><strong>Current Address:</strong> {{ $student->current_address ?? 'N/A' }}</p>

                        <div class="col-span-2 border-b pb-2 mt-4"><h4 class="font-bold text-indigo-700 uppercase text-[10px]">Parents Data</h4></div>
                        <p class="text-sm"><strong>Father:</strong> {{ $student->father_name ?? 'N/A' }} ({{ $student->father_occupation ?? 'N/A' }})</p>
                        <p class="text-sm"><strong>Mother:</strong> {{ $student->mother_name ?? 'N/A' }} ({{ $student->mother_occupation ?? 'N/A' }})</p>
                        <p class="text-sm"><strong>Parent Tel. No:</strong> {{ $student->parent_tel ?? 'N/A' }}</p>
                        <p class="text-sm col-span-2 text-gray-600"><strong>Address:</strong> {{ $student->parent_address ?? 'N/A' }}</p>

                        <div class="col-span-2 border-b pb-2 mt-4"><h4 class="font-bold text-indigo-700 uppercase text-[10px]">Guardians Data</h4></div>
                        <p class="text-sm"><strong>Guardian:</strong> {{ $student->guardian_name ?? 'N/A' }}</p>
                        <p class="text-sm col-span-2 text-gray-600"><strong>Address:</strong> {{ $student->guardian_address ?? 'N/A' }}</p>

                        <div class="col-span-2 bg-gray-50 p-4 rounded mt-4 text-[10px] text-gray-500 border border-dashed">
                            <div class="grid grid-cols-2 gap-2">
                                <p><strong>Basis of Admission:</strong> {{ $student->basis_of_admission ?? 'N/A' }}</p>
                                <p><strong>Date of Admission:</strong> {{ $student->date_of_admission ?? 'N/A' }}</p>
                                <p><strong>Encoded By:</strong> {{ $student->encoder->name ?? 'System' }}</p>
                                <p><strong>Updated By:</strong> {{ $student->updater->name ?? 'System' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="tab === 'education'" class="bg-white shadow-sm sm:rounded-lg p-6 border" x-cloak>
                    <h4 class="font-bold text-indigo-700 border-b pb-2 mb-4 uppercase text-[10px]">Educational Attainment</h4>

                    <div class="mb-4 text-right">
                        <a href="{{ route('students.education.edit', $student) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-[10px] text-white uppercase tracking-widest hover:bg-indigo-700 transition">
                            Edit Education
                        </a>
                    </div>

                    @php
                        $primarySecondaryLevels = [
                            'Nursery Education',
                            'Junior Kinder Education',
                            'Senior Kinder Education',
                            'Primary Education',
                            'Intermediate Education',
                            'Secondary Education',
                        ];

                        $higherEducationLevels = [
                            'Bacaluarte/ Teritary',
                            'Masteral / Graduate',
                            'Doctorate / Post Graduate',
                        ];

                        $primarySecondaryEducation = $student->education
                            ->where('education_group', 'primary_secondary')
                            ->keyBy('level');

                        $higherEducation = $student->education
                            ->where('education_group', 'higher_education')
                            ->keyBy('level');
                    @endphp

                    <h5 class="font-bold text-gray-700 mb-2 uppercase text-[10px]">Primary and Secondary Education</h5>
                    <div class="overflow-x-auto mb-6">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">Education Level</th>
                                    <th class="px-4 py-2 text-left">School</th>
                                    <th class="px-4 py-2 text-left">Inclusive Dates</th>
                                    <th class="px-4 py-2 text-left">Date Entered</th>
                                    <th class="px-4 py-2 text-left">Date Graduated</th>
                                    <th class="px-4 py-2 text-left">Honors and Awards</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white text-xs">
                                @foreach($primarySecondaryLevels as $level)
                                    @php
                                        $edu = $primarySecondaryEducation->get($level);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">{{ $level }}</td>
                                        <td class="px-4 py-3">{{ $edu->school_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu->inclusive_dates ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu && $edu->date_entered ? $edu->date_entered->format('Y-m-d') : 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu && $edu->date_graduated ? $edu->date_graduated->format('Y-m-d') : 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu->honors_awards ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h5 class="font-bold text-gray-700 mb-2 uppercase text-[10px]">College / Graduate / Post Graduate Education</h5>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50 text-[10px] uppercase font-bold text-gray-500">
                                <tr>
                                    <th class="px-4 py-2 text-left">Education Level</th>
                                    <th class="px-4 py-2 text-left">School</th>
                                    <th class="px-4 py-2 text-left">Course/Major</th>
                                    <th class="px-4 py-2 text-left">Date Graduated</th>
                                    <th class="px-4 py-2 text-left">SO No.</th>
                                    <th class="px-4 py-2 text-left">Thesis</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white text-xs">
                                @foreach($higherEducationLevels as $level)
                                    @php
                                        $edu = $higherEducation->get($level);
                                    @endphp
                                    <tr>
                                        <td class="px-4 py-3">{{ $level }}</td>
                                        <td class="px-4 py-3">{{ $edu->school_name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu->course_major ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu && $edu->date_graduated ? $edu->date_graduated->format('Y-m-d') : 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu->so_number ?? 'N/A' }}</td>
                                        <td class="px-4 py-3">{{ $edu->thesis ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="tab === 'documents'" class="bg-white shadow-sm sm:rounded-lg p-6 border" x-cloak>
                    <h4 class="font-bold text-indigo-700 border-b pb-2 mb-4 uppercase text-[10px]">Document Photo Upload</h4>

                    <form action="{{ route('students.documents.store', $student) }}" method="POST" enctype="multipart/form-data" class="mb-6 p-4 bg-gray-50 rounded-lg border">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <input type="text" name="document_name" placeholder="Document Name" required class="rounded-md border-gray-300 text-xs">
                            <input type="file" name="document_file" required class="text-[10px]">
                        </div>
                        <button type="submit" class="mt-3 bg-indigo-600 text-white px-4 py-2 rounded-md font-bold text-[10px] uppercase">Upload</button>
                    </form>

                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($student->documents as $doc)
                            <div class="border rounded bg-white shadow-sm overflow-hidden">
                                <img src="{{ asset('storage/' . $doc->file_path) }}" class="h-24 w-full object-cover">
                                <div class="p-2 text-center bg-gray-50 border-t">
                                    <p class="text-[9px] font-bold truncate text-gray-700">{{ $doc->document_name }}</p>
                                    <form action="{{ route('students.documents.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Delete document?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[8px] text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div> </div> </div>
</div>
@endsection
