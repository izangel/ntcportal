<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class List - {{ $section->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; background: white; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body class="bg-gray-100 p-8" onload="window.print()">

    <div class="no-print mb-6 flex justify-center">
        <button onclick="window.print()" class="bg-blue-600 text-white px-6 py-2 rounded shadow">
            Confirm Print
        </button>
    </div>

    <div class="max-w-4xl mx-auto bg-white p-10 shadow-lg border border-gray-200">
        
        <div class="text-center border-b-2 border-black pb-4 mb-6">
            <h1 class="text-xl font-bold uppercase">NORTHLINK TECHNOLOGICAL COLLEGE</h1>
            <p class="text-sm italic">Office of the Registrar</p>
            <h2 class="text-lg font-bold mt-4 uppercase">Official Class List</h2>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
            <div>
                <p><strong>Section:</strong> <span class="uppercase">{{ $section->name }}</span></p>
                <p><strong>Semester:</strong> {{ $semester }}</p>
            </div>
            <div class="text-right">
                <p><strong>Academic Year:</strong> {{ $ay->start_year }}-{{ $ay->end_year }}</p>
                <p><strong>Date Generated:</strong> {{ now()->format('F d, Y') }}</p>
            </div>
        </div>

        <table class="w-full border-collapse border border-black text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border border-black px-2 py-1 w-12 text-center">#</th>
                    <th class="border border-black px-4 py-1 text-left">Student ID</th>
                    <th class="border border-black px-4 py-1 text-left">Full Name</th>
                    <th class="border border-black px-4 py-1 text-center w-24">Gender</th>
                    <th class="border border-black px-4 py-1 text-left">Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr>
                    <td class="border border-black px-2 py-1 text-center">{{ $index + 1 }}</td>
                    <td class="border border-black px-4 py-1">{{ $student->student_id }}</td>
                    <td class="border border-black px-4 py-1 font-bold uppercase">
                        {{ $student->last_name }}, {{ $student->first_name }}
                    </td>
                    <td class="border border-black px-4 py-1 text-center">{{ $student->gender ?? '---' }}</td>
                    <td class="border border-black px-4 py-1 text-gray-300">________________</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-12 grid grid-cols-2 gap-20">
            <div class="text-center">
                <div class="border-b border-black mb-1"></div>
                <p class="text-xs uppercase font-bold">Class Adviser</p>
            </div>
            <div class="text-center">
                <div class="border-b border-black mb-1"></div>
                <p class="text-xs uppercase font-bold">Registrar</p>
            </div>
        </div>

        <div class="mt-10 text-[10px] text-gray-400 text-center">
            Note: This is a system-generated document.
        </div>
    </div>

</body>
</html>