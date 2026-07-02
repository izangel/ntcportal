<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Class List - {{ $section->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* This hides the button and the gray background during print */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; background: white !important; }
            .container-box { 
                box-shadow: none !important; 
                border: none !important; 
                margin: 0 !important; 
                width: 100% !important; 
                max-width: 100% !important;
            }
            @page { margin: 0.4cm; size: portrait; }
        }
        /* Strict row height for 50-row density */
        td { line-height: 1.1; height: 16px; padding: 1px 4px !important; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body class="bg-gray-100 p-4" onload="window.print()">

    <div class="no-print mb-4 flex justify-center">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded shadow-md text-sm font-bold transition">
            CONFIRM PRINT
        </button>
    </div>

    <div class="container-box max-w-3xl mx-auto bg-white p-6 shadow-lg border border-gray-200">
        
        <div class="text-center border-b-2 border-black pb-1 mb-2">
            <h1 class="text-base font-bold uppercase leading-tight">NORTHLINK TECHNOLOGICAL COLLEGE</h1>
            <p class="text-[10px] uppercase tracking-widest">Office of the Registrar</p>
            <h2 class="text-xs font-bold mt-1 uppercase">Official Class List</h2>
        </div>

        <div class="flex justify-between mb-1 text-[9px] uppercase font-semibold">
            <div>
                <span class="mr-4"><strong>Section:</strong> {{ $section->name }}</span>
                <span><strong>Semester:</strong> {{ $semester }}</span>
            </div>
            <div class="text-right">
                <span class="mr-4"><strong>AY:</strong> {{ $ay->start_year }}-{{ $ay->end_year }}</span>
                <span><strong>Date:</strong> {{ now()->format('m/d/Y') }}</span>
            </div>
        </div>

        <table class="w-full border-collapse border border-black text-[9px]">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-black w-6 text-center">#</th>
                    <th class="border border-black text-left">Last Name</th>
                    <th class="border border-black text-left">First Name</th>
                    <th class="border border-black text-left w-40">Signature</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $index => $student)
                <tr>
                    <td class="border border-black text-center text-[8px]">{{ $index + 1 }}</td>
                    <td class="border border-black uppercase font-bold">{{ $student->last_name }}</td>
                    <td class="border border-black uppercase">{{ $student->first_name }}</td>
                    <td class="border border-black text-gray-300">______________________________</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 grid grid-cols-2 gap-16 px-4">
            <div class="text-center">
                <div class="border-b border-black"></div>
                <p class="text-[9px] uppercase font-bold mt-1">Class Adviser</p>
            </div>
            <div class="text-center">
                <div class="border-b border-black"></div>
                <p class="text-[9px] uppercase font-bold mt-1">Registrar</p>
            </div>
        </div>

        <div class="mt-4 text-[8px] text-gray-400 text-center italic uppercase">
            *** System Generated Document - No Erasures Allowed ***
        </div>
    </div>

</body>
</html>