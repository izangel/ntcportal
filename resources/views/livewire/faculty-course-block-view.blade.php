<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">📝 Course Grading Portal</h2>
   
    <hr class="my-6">
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-indigo-500">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Select Academic Period</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="ay" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="sem" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
            </div>
            
            <div class="p-3 bg-indigo-50 rounded-lg text-sm text-indigo-700 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Filter by period to see your unique teaching schedule.</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-purple-500">
        @if ($academicYearId && $semester)
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Select Class / Schedule ({{ count($assignedBlocks) }} unique slots found):
            </label>
            
            @if (!empty($assignedBlocks))
                <div class="flex flex-col md:flex-row items-end space-y-4 md:space-y-0 md:space-x-4">
                    <div class="flex-grow">
                        <select wire:model.live="selectedBlockId" id="courseBlock" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-purple-500 text-sm">
                            <option value="">-- Select a Class --</option>
                            @foreach($assignedBlocks as $block)
                                <option value="{{ $block['id'] }}">
                                    {{ $block['course_code'] }}: {{ $block['course_name'] }} | {{ $block['schedule_string'] }} | ({{ $block['sections'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if ($selectedBlockId && !$blockSelectedAndConfirmed)
                        <button wire:click="loadSelectedBlockGrades" 
                                wire:loading.attr="disabled"
                                class="w-full md:w-auto px-8 py-2 bg-purple-600 text-white font-bold rounded-md shadow hover:bg-purple-700 transition">
                            <span wire:loading.remove wire:target="loadSelectedBlockGrades">Open Grading Sheet</span>
                            <span wire:loading wire:target="loadSelectedBlockGrades">Fetching Students...</span>
                        </button>
                    @endif
                </div>
            @else
                <p class="text-gray-500 italic">No classes assigned for this period.</p>
            @endif
        @else
            <p class="text-gray-500 italic">Please select Academic Year and Semester first.</p>
        @endif
    </div>

    @if ($blockSelectedAndConfirmed && $selectedBlockId)
        @php
            $selectedBlock = collect($assignedBlocks)->firstWhere('id', $selectedBlockId);
            // Accessing 'finalized' from the array passed from controller
            $isFinalized = $selectedBlock ? ($selectedBlock['finalized'] ?? false) : false;
        @endphp

        <div class="flex flex-col md:flex-row justify-between items-center mb-6 p-4 bg-white border-l-4 border-indigo-500 rounded-r-lg shadow-sm">
            <div>
                <h4 class="text-xl font-bold text-gray-800">{{ $selectedBlock['course_code'] }} - {{ $selectedBlock['course_name'] }}</h4>
                <p class="text-sm text-gray-600">
                    <span class="font-semibold text-indigo-600">Sections:</span> {{ $selectedBlock['sections'] }}
                    | <span class="font-semibold text-indigo-600">Schedule:</span> {{ $selectedBlock['schedule_string'] }}
                </p>
            </div>

            @if ($isFinalized)
            <div class="mt-4 md:mt-0">
                <button wire:click="printFinalizedGrades" 
                        wire:loading.attr="disabled"
                        class="px-5 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 flex items-center shadow transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Report of Rating
                </button>
            </div>
            @endif
        </div>

        @livewire('grade-input-form', 
            ['blockId' => $selectedBlockId], 
            key('grade-input-' . $selectedBlockId . '-' . $syncKey)
        )

        @if (session()->has('message'))
            <div class="mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
                {!! session('message') !!}
            </div>
        @endif

        @if ($isFinalized)
            <div class="mt-12 bg-white rounded-xl shadow-lg border-t-4 border-red-600 overflow-hidden">
                <div class="p-6">
                    <h3 class="text-2xl font-bold text-red-700 mb-2">INC Resolution Panel</h3>
                    <p class="text-sm text-gray-500 mb-6">Grades for this merged block are locked. Use this panel to update INC records only.</p>
                    @livewire('resolve-inc-grade', 
                        ['blockId' => $selectedBlockId], 
                        key('resolve-inc-' . $selectedBlockId)
                    )
                </div>
            </div>
        @endif
    @else
        <div class="text-center text-gray-400 mt-10 p-12 bg-white shadow rounded-xl border-2 border-dashed">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <p>Select a course block and click "Open Grading Sheet" to view students.</p>
        </div>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('triggerPrint', (event) => {
            const data = event[0];
            const logoUrl = window.location.origin + '/images/ntc-logo.jpeg';

            // Helper function to determine Remarks based on Rating
            const getRemarks = (grade) => {
                if (!grade) return '';
                const g = grade.toString().toUpperCase();
                if (g === 'INC') return 'INCOMPLETE';
                if (g === 'DRP') return 'DROPPED';
                if (g === '5.0') return 'FAILED';
                
                const numGrade = parseFloat(g);
                if (!isNaN(numGrade) && numGrade <= 3.0) return 'PASSED';
                if (!isNaN(numGrade) && numGrade > 3.0) return 'FAILED';
                return '';
            };

            let printContent = `
                <style>
                    @page { size: portrait; margin: 0.4in; }
                    body { font-family: "Times New Roman", Times, serif; padding: 0; color: #000; line-height: 1.1; font-size: 10.5px; }
                    
                    .letterhead { display: flex; align-items: center; justify-content: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
                    .letterhead img { height: 55px; margin-right: 15px; }
                    .header-text h1 { font-size: 15px; margin: 0; font-weight: bold; text-transform: uppercase; }
                    .header-text p { font-size: 9px; margin: 0; }
                    
                    .report-title { font-size: 14px; font-weight: bold; text-align: center; margin: 10px 0; text-transform: uppercase; }
                    
                    .meta-table { width: 100%; margin-bottom: 10px; font-size: 10.5px; border-collapse: collapse; }
                    .meta-table td { padding: 1px 0; vertical-align: top; }
                    
                    .grade-table { width: 100%; border-collapse: collapse; margin-top: 5px; table-layout: fixed; }
                    .grade-table th, .grade-table td { 
                        border: 1px solid #000; 
                        padding: 2px 4px; 
                        text-align: left; 
                        font-size: 9.5px; 
                        word-wrap: break-word;
                    }
                    .grade-table th { background: #f2f2f2; text-transform: uppercase; font-weight: bold; font-size: 9px; text-align: center; }
                    .text-center { text-align: center; }
                    .font-bold { font-weight: bold; }
                    
                    .sig-container { margin-top: 20px; display: flex; justify-content: space-between; flex-wrap: wrap; }
                    .sig-box { width: 45%; margin-bottom: 15px; }
                    .sig-label-bold { font-size: 10.5px; font-weight: bold; margin-bottom: 18px; }
                    .sig-name-line { border-bottom: 1px solid #000; font-weight: bold; text-transform: uppercase; font-size: 11px; padding-bottom: 1px; text-align: center; }
                    .sig-sub-label { font-size: 9px; margin-top: 1px; text-align: center; }
                    
                    .registrar-container { width: 100%; display: flex; justify-content: center; margin-top: 5px; }
                </style>

                <div class="letterhead">
                    <img src="${logoUrl}" onerror="this.src='https://via.placeholder.com/55?text=LOGO'">
                    <div class="header-text">
                        <h1>NORTHLINK TECHNOLOGICAL COLLEGE</h1>
                        <p>Panabo City, Davao del Norte</p>
                        <p>Official Academic Records and Grading System</p>
                    </div>
                </div>

                <div class="report-title">Report of Rating</div>

                <table class="meta-table">
                    <tr>
                        <td width="12%"><strong>COURSE:</strong></td>
                        <td width="48%">${data.courseCode} - ${data.courseName}</td>
                        <td width="15%"><strong>SCHEDULE:</strong></td>
                        <td width="25%" align="right">${data.scheduleString}</td>
                    </tr>
                    <tr>
                        <td><strong>PERIOD:</strong></td>
                        <td>${data.academicPeriod}</td>
                        <td><strong>SECTIONS:</strong></td>
                        <td align="right">${data.blockDetails}</td>
                    </tr>
                </table>

                <table class="grade-table">
                    <thead>
                        <tr>
                            <th width="5%">No.</th>
                            <th width="42%" style="text-align: left;">Student Name</th>
                            <th width="25%" style="text-align: left;">Program & Section</th>
                            <th width="12%">Rating</th>
                            <th width="16%">Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.students.map((s, index) => {
                            const remark = getRemarks(s.finalGrade);
                            return `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td style="text-transform: uppercase; white-space: nowrap; overflow: hidden;">${s.studentName}</td>
                                <td style="font-size: 8.5px;">${s.section}</td>
                                <td class="text-center font-bold">${s.finalGrade || '-'}</td>
                                <td class="text-center" style="font-size: 8px;">${remark}</td>
                            </tr>
                        `}).join('')}
                    </tbody>
                </table>

                <div class="sig-container">
                    <div class="sig-box">
                        <div class="sig-label-bold">Prepared by:</div>
                        <div class="sig-name-line">${data.teacherName}</div>
                        <div class="sig-sub-label">Instructor/Professor</div>
                    </div>
                    
                    <div class="sig-box">
                        <div class="sig-label-bold">Checked & Approved by:</div>
                        <div class="sig-name-line">&nbsp;</div>
                        <div class="sig-sub-label">Program Head</div>
                    </div>

                    <div class="registrar-container">
                        <div class="sig-box" style="width: 35%; margin-bottom: 0;">
                            <div class="sig-label-bold" style="text-align: center; margin-bottom: 18px;">Received by:</div>
                            <div class="sig-name-line">GENEROSE A. SABUDIN</div>
                            <div class="sig-sub-label">College Registrar</div>
                        </div>
                    </div>
                </div>
            `;

            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>Report of Rating</title></head><body>' + printContent + '</body></html>');
            printWindow.document.close();
            
            setTimeout(() => {
                printWindow.print();
                printWindow.close();
            }, 800);
        });
    });
</script>