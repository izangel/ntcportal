<div class="p-6 bg-gray-50 min-h-screen">
    <h2 class="text-3xl font-bold text-gray-800 mb-6">📝 Course Grading Portal</h2>
   
    <hr class="my-6">
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-indigo-500">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Select Academic Period</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div>
                <label for="ay" class="block text-sm font-medium text-gray-700">Academic Year</label>
                <select id="ay" wire:model.live="academicYearId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select AY</option>
                    @foreach ($academicYears as $ay)
                        <option value="{{ $ay->id }}">{{ $ay->start_year }} - {{ $ay->end_year }}</option> 
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="sem" class="block text-sm font-medium text-gray-700">Semester</label>
                <select id="sem" wire:model.live="semester" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Semester</option>
                    @foreach ($semesters as $sem)
                        <option value="{{ $sem }}">{{ $sem }} Semester</option>
                    @endforeach
                </select>
            </div>
            
            <div class="p-3 bg-indigo-50 rounded-lg text-sm text-indigo-700 flex items-center">
                <p class="font-semibold">Select your filters to view assigned blocks.</p>
            </div>
        </div>
    </div>
    
    <div class="bg-white shadow-lg rounded-xl p-6 mb-8 border-t-4 border-purple-500">
        @if ($academicYearId && $semester)
            <label for="block" class="block text-sm font-medium text-gray-700 mb-2">
                Select Course Block to Grade ({{ $assignedBlocks->count() }} found):
            </label>
            
            @if ($assignedBlocks->isNotEmpty())
                <div class="flex items-center space-x-4">
                    <select wire:model.live="selectedBlockId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Select a Class</option>
                        @foreach($assignedBlocks as $block)
                            <option value="{{ $block->id }}">
                                {{ $block->course->code }} - {{ $block->combined_sections }} ({{ $block->schedule_string }})
                            </option>
                        @endforeach
                    </select>

                    @if ($selectedBlockId && !$blockSelectedAndConfirmed)
                        <button wire:click="loadSelectedBlockGrades" 
                                wire:loading.attr="disabled"
                                class="px-6 py-2 whitespace-nowrap bg-purple-600 text-white font-semibold rounded-md shadow hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition duration-150">
                            <span wire:loading.remove wire:target="loadSelectedBlockGrades">Load Grades</span>
                            <span wire:loading wire:target="loadSelectedBlockGrades">Loading...</span>
                        </button>
                    @elseif ($selectedBlockId && $blockSelectedAndConfirmed)
                        <span class="px-6 py-2 text-sm text-green-600 border border-green-300 rounded-md bg-green-50 whitespace-nowrap">Grades Loaded</span>
                    @endif
                </div>
            @else
                <p class="text-gray-500 italic">No blocks assigned to you for the selected Academic Period.</p>
            @endif
        @else
            <p class="text-gray-500 italic">Please select both Academic Year and Semester to view your assigned blocks.</p>
        @endif
    </div>

    {{-- 🔑 Only render the child components IF blockSelectedAndConfirmed is TRUE 🔑 --}}
    @if ($blockSelectedAndConfirmed && $selectedBlockId)
        @php
            // Find the selected block object and determine status
            $selectedBlock = $assignedBlocks->firstWhere('id', $selectedBlockId);
            $isFinalized = $selectedBlock ? $selectedBlock->finalized : false;
        @endphp

        {{-- NEW: Print Button Area (Visible only when finalized) --}}
        @if ($isFinalized)
        <div class="flex justify-end mb-4">
            <button wire:click="printFinalizedGrades" 
                    wire:loading.attr="disabled"
                    class="px-6 py-3 bg-green-600 text-white font-semibold rounded-lg shadow-md hover:bg-green-700 transition duration-150 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 4v3h.5a.5.5 0 01.5.5V11a.5.5 0 01-.5.5H5v3h10v-3h-.5a.5.5 0 01-.5-.5V7.5a.5.5 0 01.5-.5H15V4h1a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2h1zM8 9a1 1 0 100-2 1 1 0 000 2zM9 9a1 1 0 100-2 1 1 0 000 2zM10 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                <span wire:loading.remove wire:target="printFinalizedGrades">Print Final Grades</span>
                <span wire:loading wire:target="printFinalizedGrades">Preparing Report...</span>
            </button>
        </div>
        @endif
        {{-- END NEW: Print Button Area --}}

        {{-- 🔑 Use $syncKey to force reload after INC resolution 🔑 --}}
        @livewire('grade-input-form', 
            ['blockId' => $selectedBlockId], 
            key('grade-input-' . $selectedBlockId . '-' . $syncKey)
        )

       
        @if (session()->has('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded" role="alert">
                <p>{!! session('message') !!}</p>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded" role="alert">
                <p>{!! session('error') !!}</p>
            </div>
        @endif
        

        @if ($isFinalized)
            <h3 class="text-2xl font-bold text-red-700 mb-4 mt-8">Finalized Course Block: INC Resolution Interface</h3>
            @livewire('resolve-inc-grade', 
                ['blockId' => $selectedBlockId], 
                key('resolve-inc-' . $selectedBlockId)
            )
        @endif
    @else
        <p class="text-center text-gray-500 mt-10 p-6 bg-white shadow-lg rounded-xl">
            @if ($selectedBlockId)
                Click the **"Load Grades"** button to view the enrollment data and begin grading for the selected block.
            @else
                Select a Course Block above to begin grading.
            @endif
        </p>
    @endif
    
</div> 


<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('triggerPrint', (event) => {
            const data = event[0];
            
            const teacherName = data.teacherName || '_________________________';

            let printContent = `
                <style>
                    /* Print-specific CSS */
                    body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
                    
                    /* --- Header/Report Styles --- */
                    .report-container { width: 100%; margin: 0 auto; }
                    .letterhead { 
                        display: flex; 
                        align-items: center; 
                        justify-content: center; 
                        padding-bottom: 10px;
                        border-bottom: 3px double #000;
                        margin-bottom: 15px;
                    }
                    .letterhead img {
                        height: 75px;
                        width: auto;
                        margin-right: 20px;
                    }
                    .header-text {
                        text-align: left;
                        line-height: 1.2;
                    }
                    .header-text h1 { 
                        font-size: 20px; 
                        margin: 0; 
                        color: #000;
                    }
                    .header-text p { 
                        font-size: 12px; 
                        margin: 0; 
                        color: #555;
                    }
                    .report-title { font-size: 18px; font-weight: bold; text-align: center; margin-bottom: 15px; }
                    .report-details table { width: 100%; font-size: 12px; }
                    .report-details table td { padding: 3px 0; }
                    .grade-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    .grade-table th, .grade-table td { border: 1px solid #000; padding: 8px; text-align: left; font-size: 14px; }
                    .grade-table th { background-color: #f0f0f0; }
                    
                    /* --- Signature Styles (UPDATED) --- */
                    .signatures { 
                        margin-top: 40px; 
                        display: flex; 
                        justify-content: space-between; 
                        font-size: 12px;
                    }
                    .received-by-section {
                        justify-content: center;
                        margin-top: 20px;
                    }
                    .signature-column { 
                        width: 45%;
                        text-align: center; 
                    }
                    .received-by-section .signature-column {
                        width: 35%;
                    }
                    .role-title {
                        font-size: 14px;
                        margin-bottom: 5px;
                        text-align: center;
                    }
                    .signature-line { 
                        border-bottom: 1px solid #000; 
                        height: 18px; 
                        margin-bottom: 5px;
                        font-weight: bold;
                        text-transform: uppercase;
                        padding-top: 5px;
                    }
                    .role-label {
                        text-align: center;
                        margin-top: 0;
                        font-size: 14px;
                    }

                    .finalized-status { 
                        text-align: center; 
                        margin-top: 30px; 
                        font-weight: bold; 
                        color: green; 
                        border-top: 1px solid #ccc; 
                        padding-top: 10px; 
                    }
                </style>
                <div class="report-container">
                    <div class="letterhead">
                        <img src="/images/ntc-logo.jpeg" alt="NTC Letterhead Logo">
                        <div class="header-text">
                            <h1>NORTHLINK TECHNOLOGICAL COLLEGE</h1>
                            <p>Official Academic Records and Grading System</p>
                            <p>Contact No: 0939 384 2969 | Email: ntcregistrar@northlink.edu.ph</p>
                        </div>
                    </div>
                    
                    <div class="report-title">FINALIZED GRADE REPORT</div>

                    <div class="report-details">
                        <table>
                            <tr>
                                <td><strong>Course:</strong> ${data.courseCode}-${data.courseName}</td>
                                <td style="text-align: right;"><strong>Teacher:</strong> ${data.teacherName}</td>
                            </tr>
                            <tr>
                                <td><strong>Academic Period:</strong> ${data.academicPeriod} (${data.semester} Semester)</td>
                                <td style="text-align: right;"><strong>Block:</strong> ${data.blockDetails}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <table class="grade-table">
                        <thead>
                            <tr>
                                <th style="width: 15%;">Student ID</th>
                                <th style="width: 65%;">Student Name</th>
                                <th style="width: 20%; text-align: center;">Final Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.students.map(student => `
                                <tr>
                                    <td>${student.studentId}</td>
                                    <td>${student.studentName}</td>
                                    <td style="text-align: center;"><strong>${student.finalGrade}</strong></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>

                    <div class="signatures">
                        <div class="signature-column">
                            <p class="role-title">Prepared by:</p>
                            <div class="signature-line">${teacherName.toUpperCase()}</div>
                            <p class="role-label">Instructor/Prof</p>
                        </div>
                        
                        <div class="signature-column">
                            <p class="role-title">Checked and Approved by:</p>
                            <div class="signature-line">&nbsp;</div>
                            <p class="role-label">Program Head</p>
                        </div>
                    </div>

                    <div class="signatures received-by-section">
                        <div class="signature-column">
                            <p class="role-title">Received by:</p>
                            <div class="signature-line">GENEROSE A. SABUDIN</div>
                            <p class="role-label">College Registrar</p>
                        </div>
                    </div>
                    
                    <div class="finalized-status">
                        CONFIRMED AND FINALIZED GRADES
                    </div>
                </div>
            `;

            // Open a new window using about:blank and features to minimize UI
            const printWindow = window.open('about:blank', 'GradePrintWindow', 'height=600,width=800,status=no,location=no,toolbar=no');
            
            if (!printWindow) {
                alert("The print window could not be opened. Please check your browser's pop-up blocker settings.");
                return;
            }
            
            printWindow.document.write('<html><head><title>Report of Rating</title></head><body>');
            printWindow.document.write(printContent);
            printWindow.document.write('</body></html>');
            printWindow.document.close();
            
            printWindow.onload = function() {
                printWindow.print();
            };
        });
    });
</script>