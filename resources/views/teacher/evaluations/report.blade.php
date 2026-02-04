@extends('layouts.admin')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="bg-gray-50 min-h-screen py-10 print:bg-white print:py-0" x-data="{ activeTab: 'student' }">
    <div class="max-w-5xl mx-auto px-4 print:max-w-full print:px-0">

        <div class="flex justify-between items-start border-b-4 border-gray-900 pb-6 mb-8">
            <div>
                <h1 class="text-4xl font-black text-gray-900 uppercase tracking-tighter">Performance Evaluation Report</h1>
                <div class="mt-2 flex gap-4 text-sm font-bold text-gray-500 uppercase tracking-widest">
                    <span>Faculty: {{ auth()->user()->employee->first_name }} {{ auth()->user()->employee->last_name }}</span>
                    <span>•</span>
                    <span>{{ $academicYear->start_year }}-{{ $academicYear->end_year }}</span>
                    <span>•</span>
                    <span>{{ $semester }} Semester</span>
                </div>
            </div>
            <div class="text-right">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.3em] mb-1">Institutional Score</p>
                <div class="text-5xl font-black text-indigo-600 leading-none">{{ number_format($overallScore, 2) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 print:grid-cols-4">
            @foreach(['student', 'peer', 'supervisor', 'self'] as $type)
                @php $data = $breakdown[$type]['meta'] ?? null; @endphp
                <div class="bg-white border-2 border-gray-100 p-5 rounded-2xl print:border-gray-200">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">{{ $type }} Mean</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-gray-900">{{ $data ? number_format($data['average'], 2) : 'N/A' }}</span>
                        <span class="text-[10px] font-bold text-gray-300">/ 5.0</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12 page-break-avoid">
            <div class="md:col-span-2 bg-white border border-gray-200 rounded-3xl p-6">
                <h3 class="text-[10px] font-black text-gray-900 uppercase tracking-widest mb-6">360° Visual Comparison</h3>
                <div class="h-64"><canvas id="comparisonChart"></canvas></div>
            </div>
            <div class="bg-gray-900 text-white rounded-3xl p-8 flex flex-col justify-center">
                <h4 class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mb-3">Professional Insight</h4>
                <p class="text-sm leading-relaxed text-gray-300 italic">
                    {{-- Automated Gap Logic --}}
                    @php
                        $self = $breakdown['self']['meta']['average'] ?? 0;
                        $others = ($overallScore * 4 - $self) / 3;
                        $gap = $self - $others;
                    @endphp
                    @if(abs($gap) < 0.3)
                        Your self-assessment is highly synchronized with institutional feedback, demonstrating strong professional self-awareness.
                    @elseif($gap > 0)
                        Your self-rating is {{ number_format($gap, 1) }} points higher than the external mean, suggesting potential blind spots in perceived impact.
                    @else
                        External evaluators rate your performance {{ number_format(abs($gap), 1) }} points higher than your self-assessment.
                    @endif
                </p>
            </div>
        </div>

        <div class="flex bg-white p-1 rounded-2xl border border-gray-200 mb-8 print:hidden">
            @foreach(['student', 'peer', 'supervisor', 'self'] as $tab)
                <button @click="activeTab = '{{ $tab }}'"
                    :class="activeTab === '{{ $tab }}' ? 'bg-indigo-600 text-white' : 'text-gray-500'"
                    class="flex-1 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    {{ $tab }}
                </button>
            @endforeach
        </div>

        @foreach(['student', 'peer', 'supervisor', 'self'] as $type)
            <div x-show="activeTab === '{{ $type }}'" class="print:block mb-12" :class="activeTab === '{{ $type }}' ? 'block' : 'hidden print:block'">
                <div class="flex items-center gap-4 mb-6">
                    <h2 class="text-xl font-black text-gray-900 uppercase tracking-tight">{{ $type }} Detailed Analysis</h2>
                    <div class="h-px flex-1 bg-gray-200"></div>
                </div>

                @if(isset($breakdown[$type]))
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 page-break-avoid">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-6 py-3 text-[9px] font-black text-gray-400 uppercase">Criteria</th>
                                    <th class="px-6 py-3 text-[9px] font-black text-gray-400 uppercase text-right">Mean</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($breakdown[$type]['questions'] as $key => $score)
                                    <tr>
                                        <td class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-tight">{{ $key }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-black text-gray-900">{{ number_format($score, 2) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 page-break-avoid">
                        @foreach($breakdown[$type]['meta']['feedback'] as $fb)
                            @if($fb['helped'] || $fb['improved'])
                                <div class="p-5 border border-gray-200 rounded-xl bg-white">
                                    @if($fb['helped'])
                                        <p class="text-xs text-gray-600 mb-2"><strong class="text-green-600 uppercase text-[8px] block mb-1">Strength:</strong> "{{ $fb['helped'] }}"</p>
                                    @endif
                                    @if($fb['improved'])
                                        <p class="text-xs text-gray-600"><strong class="text-amber-600 uppercase text-[8px] block mb-1">Growth Area:</strong> "{{ $fb['improved'] }}"</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-sm italic text-gray-400">No data submitted for this category.</p>
                @endif
            </div>
        @endforeach

        <div class="hidden print:block mt-20 border-t border-gray-200 pt-8 text-center">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                Generated via Institutional 360-Degree Feedback System • {{ now()->format('M d, Y') }}
            </p>
        </div>

       

        <button onclick="generateCompactPrint()" class="flex items-center gap-2 bg-white border border-gray-200 px-5 py-2.5 rounded-xl text-[11px] font-black uppercase tracking-widest text-gray-600 hover:bg-gray-50 hover:border-indigo-300 transition-all shadow-sm">
            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print
        </button>

    </div>
</div>

<style>
    @media print {
        /* Force all hidden categories to show during print */
        div[x-show] { display: block !important; }
        .page-break-avoid { page-break-inside: avoid; }
        body { background: white !important; }
        .bg-gray-50, .bg-indigo-50 { background: white !important; }
        .text-indigo-600 { color: #4f46e5 !important; }
        .bg-gray-900 { background: #111827 !important; color: white !important; -webkit-print-color-adjust: exact; }
    }
</style>

<script>
    document.addEventListener('alpine:init', () => {
        const ctx = document.getElementById('comparisonChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['STUDENTS', 'PEERS', 'SUPERVISOR', 'SELF'],
                datasets: [{
                    data: [
                        {{ $breakdown['student']['meta']['average'] ?? 0 }},
                        {{ $breakdown['peer']['meta']['average'] ?? 0 }},
                        {{ $breakdown['supervisor']['meta']['average'] ?? 0 }},
                        {{ $breakdown['self']['meta']['average'] ?? 0 }}
                    ],
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#374151'],
                    borderRadius: 4
                }]
            },
            options: { 
                scales: { y: { beginAtZero: true, max: 5 } },
                plugins: { legend: { display: false } }
            }
        });
    });


function generateCompactPrint() {
    // 1. Data Retrieval
    const rawData = @json($breakdown);
    
    // Variables from Laravel
    const teacherName = "{{ auth()->user()->employee->first_name }} {{ auth()->user()->employee->last_name }}";
    const academicYear = "{{ $academicYear->start_year }}-{{ $academicYear->end_year }}";
    const semester = "{{ $semester }}";
    const overallScore = "{{ number_format($overallScore, 2) }}";

    if (!rawData) {
        alert("No evaluation data found.");
        return;
    }

    const printWindow = window.open('', '_blank', 'width=950,height=1000');
    if (!printWindow) {
        alert("Please allow popups for this website.");
        return;
    }

    // Sequence: Self -> Peer -> Supervisor -> Student
    const categoriesOrder = ['self', 'peer', 'supervisor', 'student'];

    let htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Report - ${teacherName}</title>
            <style>
                @page { size: portrait; margin: 10mm; }
                body { font-family: 'Arial', sans-serif; font-size: 8pt; line-height: 1.2; color: #000; margin: 0; padding: 0; }
                
                /* Header Section */
                .report-header { border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
                .teacher-info h1 { margin: 0; font-size: 14pt; text-transform: uppercase; font-weight: 900; letter-spacing: -0.5px; }
                .teacher-info p { margin: 2px 0 0 0; font-size: 9pt; font-weight: bold; color: #444; text-transform: uppercase; }
                
                .score-display { text-align: right; }
                .score-label { font-size: 7pt; font-weight: 900; color: #666; margin-bottom: -2px; }
                .score-value { font-size: 22pt; font-weight: 900; color: #4f46e5; }

                /* Tables */
                .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .summary-table th, .summary-table td { border: 1px solid #000; padding: 4px; text-align: center; }
                .summary-table th { background: #f2f2f2; text-transform: uppercase; font-size: 6.5pt; letter-spacing: 0.5px; }
                .summary-table td { font-size: 11pt; font-weight: 900; }

                /* Layout Grid */
                .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
                .section { border: 0.75px solid #000; padding: 6px; break-inside: avoid; border-radius: 4px; }
                .section-header { background: #f9fafb; font-weight: 900; text-transform: uppercase; font-size: 7.5pt; padding: 3px 6px; border-bottom: 0.75px solid #000; margin: -6px -6px 6px -6px; display: flex; justify-content: space-between; }
                
                .score-table { width: 100%; border-collapse: collapse; }
                .score-table td { padding: 2px 0; border-bottom: 0.5px solid #eee; font-size: 7pt; }
                .score-col { text-align: right; font-weight: bold; width: 35px; font-size: 8pt; }
                
                /* Qualitative Feedback Table */
                .qual-title { text-transform: uppercase; font-size: 9pt; font-weight: 900; margin: 15px 0 6px 0; border-bottom: 1.5px solid #000; padding-bottom: 2px; }
                .feedback-table { width: 100%; border-collapse: collapse; table-layout: fixed; border: 0.75px solid #000; }
                .feedback-table th, .feedback-table td { border: 0.75px solid #000; padding: 5px; font-size: 7pt; vertical-align: top; word-wrap: break-word; line-height: 1.3; }
                .feedback-table th { background: #f2f2f2; text-align: left; text-transform: uppercase; font-size: 6.5pt; }

                .tag { font-size: 6.5pt; font-weight: 900; border: 1px solid #000; padding: 0 4px; border-radius: 2px; background: #fff; }
            </style>
        </head>
        <body>
            <div class="report-header">
                <div class="teacher-info">
                    <h1>Faculty Performance Report - ${teacherName}</h1>
                    <p>AY: ${academicYear} | ${semester} Semester</p>
                </div>
                <div class="score-display">
                    <div class="score-label">OVERALL RATING</div>
                    <div class="score-value">${overallScore}</div>
                </div>
            </div>

            <table class="summary-table">
                <thead>
                    <tr>${categoriesOrder.map(type => `<th>${type} Mean</th>`).join('')}</tr>
                </thead>
                <tbody>
                    <tr>
                        ${categoriesOrder.map(type => {
                            const avg = rawData[type]?.meta?.average || 0;
                            return `<td>${parseFloat(avg).toFixed(2)}</td>`;
                        }).join('')}
                    </tr>
                </tbody>
            </table>

            <div class="grid">
    `;

    // Generate detailed category boxes
    categoriesOrder.forEach(type => {
        const cat = rawData[type];
        if (cat && cat.meta) {
            htmlContent += `
                <div class="section">
                    <div class="section-header">
                        <span>${type} Detailed Result</span>
                        <span class="tag">n=${cat.meta.count || 0}</span>
                    </div>
                    <table class="score-table">
                        ${cat.questions ? Object.entries(cat.questions).map(([q, val]) => `
                            <tr>
                                <td style="font-weight: bold; width: 15px; color: #444;">${q}</td>
                                <td>Performance Criterion Result</td>
                                <td class="score-col">${parseFloat(val).toFixed(2)}</td>
                            </tr>
                        `).join('') : ''}
                    </table>
                </div>
            `;
        }
    });

    htmlContent += `</div>`; // Close Grid

    // --- Student Feedback Section ---
    const studentCat = rawData['student'] || {};
    const meta = studentCat.meta || {};
    // Object.values ensures the loop works even if IDs are non-sequential
    const studentFeedbackList = Object.values(meta.feedback || meta.comments || []);

    htmlContent += `
        <div class="qual-title">Detailed Student Qualitative Feedback</div>
        <table class="feedback-table">
            <thead>
                <tr>
                    <th style="width: 33%;">Strengths (What helped learning)</th>
                    <th style="width: 33%;">Growth Areas (What to improve)</th>
                    <th style="width: 34%;">Additional Comments</th>
                </tr>
            </thead>
            <tbody>
    `;

    if (studentFeedbackList.length > 0) {
        studentFeedbackList.forEach(f => {
            // Only add the row if it's not completely empty
            if (f.helped || f.improved || f.comments || f.Comments) {
                htmlContent += `
                    <tr>
                        <td>${f.helped || '-'}</td>
                        <td>${f.improved || '-'}</td>
                        <td>${f.comments || f.Comments || '-'}</td>
                    </tr>
                `;
            }
        });
    } else {
        htmlContent += `<tr><td colspan="3" style="text-align:center; padding: 15px; color: #666;">No written feedback submitted by students.</td></tr>`;
    }

    htmlContent += `
            </tbody>
        </table>
        
        <div style="margin-top: 25px; border-top: 1px solid #000; pt: 5px; text-align: center; font-size: 6.5pt; color: #666; text-transform: uppercase; font-weight: bold;">
            Faculty Performance Report • Confidential Document • Generated ${new Date().toLocaleDateString()}
        </div>
    </body>
    </html>
    `;

    printWindow.document.write(htmlContent);
    printWindow.document.close();

    setTimeout(() => {
        printWindow.focus();
        printWindow.print();
    }, 500);
}
</script>
@endsection