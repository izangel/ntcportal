{{-- resources/views/reports/class-list.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class List - {{ $block->course->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print { .no-print { display: none; } body { padding: 0; } }
        table { border: 1px solid black !important; }
        th, td { border: 1px solid #ccc !important; }
    </style>
</head>
<body class="bg-gray-100 p-8">

    <div class="no-print max-w-4xl mx-auto mb-4">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded font-black text-xs uppercase shadow-lg">Print Class List</button>
    </div>

    <div class="bg-white w-[8.5in] min-h-[11in] mx-auto p-10 shadow-2xl print:shadow-none print:m-0">
        
        {{-- Header --}}
        <div class="text-center mb-6 border-b-2 border-black pb-4">
            <h1 class="text-xl font-black uppercase">Northlink Technological College</h1>
            <p class="text-[10px] font-bold uppercase tracking-widest text-gray-500">Official Class Enrollment List</p>
        </div>

        {{-- Class Info --}}
        <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded mb-6 text-[11px]">
            <div>
                <p><span class="font-black">Subject:</span> {{ $block->course->code }} - {{ $block->course->name }}</p>
                <p><span class="font-black">Faculty:</span> {{ $block->faculty->last_name }}, {{ $block->faculty->first_name }}</p>
                <p><span class="font-black">Schedule:</span> {{ $block->schedule_string }} ({{ $block->room_name }})</p>
            </div>
            <div class="text-right">
                <p><span class="font-black">Term:</span> {{ $block->semester }}</p>
                <p><span class="font-black">A.Y.:</span> {{ $block->academicYear->start_year }}-{{ $block->academicYear->end_year }}</p>
                <p><span class="font-black">Total Students:</span> {{ $students->count() }}</p>
            </div>
        </div>

        {{-- Student Table --}}
        <table class="w-full border-collapse text-[10px]">
            <thead>
                <tr class="bg-gray-200 uppercase font-black">
                    <th class="p-2 text-center w-8">#</th>
                   
                    <th class="p-2 text-left">Student Name</th>
                    <th class="p-2 text-center w-12">Gender</th>
                    <th class="p-2 text-left w-32">Program</th>
                    <th class="p-2 text-center w-24">Signature/Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr class="{{ $loop->even ? 'bg-gray-50' : '' }}">
                    <td class="p-2 text-center text-gray-400">{{ $index + 1 }}</td>
                    
                    <td class="p-2 font-black uppercase">{{ $student->last_name }}, {{ $student->first_name }}</td>
                    <td class="p-2 text-center">{{ substr($student->gender, 0, 1) }}</td>
                    <td class="p-2 uppercase text-[9px]">
                        {{-- Attempt to find the program via the section_student link --}}
                        {{ $student->sections->first()->program->name ?? 'N/A' }}
                    </td>
                    <td class="p-2"></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer --}}
        <div class="mt-12 grid grid-cols-2 gap-20">
            <div class="text-center">
                <div class="border-b border-black h-8 mb-1"></div>
                <p class="text-[9px] font-black uppercase">Instructor Signature</p>
            </div>
            <div class="text-center">
                <div class="border-b border-black h-8 mb-1"></div>
                <p class="text-[9px] font-black uppercase">Registrar Verification</p>
            </div>
        </div>
    </div>

</body>
</html>