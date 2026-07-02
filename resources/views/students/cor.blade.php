{{-- resources/views/students/cor.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>COR - {{ $student->last_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            .print-border { border: 2px solid black !important; }
        }
    </style>
</head>
<body class="bg-gray-200 py-10">

    <div class="no-print max-w-2xl mx-auto mb-4 flex justify-between items-center">
        <a href="{{ route('students.index') }}" class="text-xs font-bold text-gray-600">← Back to Registry</a>
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded font-black text-xs uppercase shadow-lg">Print Official COR</button>
    </div>

    {{-- THE CERTIFICATE --}}
    <div class="bg-white w-[8.5in] min-h-[5in] mx-auto p-8 shadow-2xl print:shadow-none print:m-0 print-border">
        
        {{-- Header --}}
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-2xl font-serif font-black tracking-widest uppercase">Northlink Technological College</h1>
            <p class="text-xs font-bold uppercase tracking-widest">Office of the Registrar</p>
            <p class="text-[10px] mt-1 font-bold">Academic Year {{ $context['ay']->start_year }}-{{ $context['ay']->end_year }} | {{ $context['semester'] }}</p>
        </div>

        <div class="text-center mb-6">
            <h2 class="text-lg font-black bg-black text-white inline-block px-4 py-1 uppercase tracking-tighter">Certificate of Registration</h2>
        </div>

        {{-- Student Details --}}
        <div class="grid grid-cols-2 gap-y-4 text-xs mb-8">
            <div class="border-b border-gray-300 pb-1 mr-4">
                <span class="text-[8px] font-black uppercase text-gray-400 block">Student ID</span>
                <span class="font-bold text-sm">{{ $student->student_id }}</span>
            </div>
            <div class="border-b border-gray-300 pb-1">
                <span class="text-[8px] font-black uppercase text-gray-400 block">Enrollment Status</span>
                <span class="font-bold text-sm uppercase">{{ $enrollment->pivot->status }}</span>
            </div>
            <div class="border-b border-gray-300 pb-1 mr-4">
                <span class="text-[8px] font-black uppercase text-gray-400 block">Full Name</span>
                <span class="font-bold text-sm uppercase">{{ $student->last_name }}, {{ $student->first_name }} {{ $student->middle_name }}</span>
            </div>
            <div class="border-b border-gray-300 pb-1">
                <span class="text-[8px] font-black uppercase text-gray-400 block">Program & Section</span>
                <span class="font-bold text-sm uppercase">{{ $enrollment->program->name }} - {{ $enrollment->name }}</span>
            </div>
        </div>

        <table class="w-full mt-6 border-collapse border border-gray-400">
    <thead>
        <tr class="bg-gray-100 text-[10px] font-black uppercase border-b border-black">
            <th class="p-2 text-left border-r border-gray-400">Code</th>
            <th class="p-2 text-left border-r border-gray-400">Subject Description</th>
            <th class="p-2 text-left border-r border-gray-400">Schedule</th>
            <th class="p-2 text-left border-r border-gray-400">Room</th>
            <th class="p-2 text-center border-r border-gray-400">Units</th>
        </tr>
    </thead>
    <tbody class="text-[10px]">
        @php
            $activeAYId = $context['ay']->id;
            // Clean the semester string to handle "2nd Semester" vs "Second Semester"
            $sem = str_replace(['Second', 'First'], ['2nd', '1st'], $context['semester']);

            $load = DB::table('student_courseblock')
                ->join('course_blocks', 'student_courseblock.course_block_id', '=', 'course_blocks.id')
                ->join('courses', 'course_blocks.course_id', '=', 'courses.id')
                ->where('student_courseblock.student_id', $student->id)
                ->where('course_blocks.academic_year_id', $activeAYId)
                ->where(function($q) use ($sem) {
                    $q->where('course_blocks.semester', $sem)
                    ->orWhere('course_blocks.semester', 'like', substr($sem, 0, 3) . '%'); 
                })
                ->select(
                    'courses.code', 
                    'courses.name', 
                    'courses.units', 
                    'course_blocks.schedule_string', 
                    'course_blocks.room_name'
                )
                ->get();
        @endphp

        @forelse($load as $item)
            <tr class="border-b border-gray-300">
                <td class="p-2 font-bold">{{ $item->code }}</td>
                <td class="p-2 uppercase font-medium">{{ $item->name }}</td>
                <td class="p-2 italic">{{ $item->schedule_string }}</td>
                <td class="p-2 text-center">{{ $item->room_name }}</td>
                <td class="p-2 text-center font-bold">
                    {{ $item->units ?? 'N/A' }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="p-8 text-center text-gray-400 uppercase font-black">No Official Load Found</td>
            </tr>
        @endforelse
    </tbody>
    <tfoot class="bg-gray-50 border-t-2 border-black">
        <tr class="font-black">
            <td colspan="4" class="p-2 text-right uppercase text-[9px]">Total Academic Units</td>
            <td class="p-2 text-center text-sm underline">{{ $load->sum('units') }}</td>
        </tr>
    </tfoot>
</table>

        {{-- Checklist and Footer --}}
        <div class="mt-20 flex justify-between items-end">
            <div class="text-[9px] text-gray-500 italic">
                <p>System Generated: {{ now()->format('M d, Y h:i A') }}</p>
                <p>Requirements: {{ $student->is_fully_enrolled ? 'VERIFIED COMPLETE' : 'INCOMPLETE/PENDING' }}</p>
            </div>
            
            <div class="text-center border-t border-black w-48">
                <p class="text-[10px] font-black uppercase mt-1">College Registrar</p>
            </div>
        </div>
    </div>

</body>
</html>