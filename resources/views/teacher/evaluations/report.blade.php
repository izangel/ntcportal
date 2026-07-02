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

        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8 print:grid-cols-5">
            {{-- Category Cards --}}
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

            {{-- Institutional Adjectival Rating Card --}}
            @php
                $score = (float)$overallScore;
                if ($score >= 4.50) { $label = 'Outstanding'; $colorClass = 'text-emerald-700 bg-emerald-50 border-emerald-200 print:bg-emerald-50'; }
                elseif ($score >= 3.50) { $label = 'Very Satisfactory'; $colorClass = 'text-blue-700 bg-blue-50 border-blue-200 print:bg-blue-50'; }
                elseif ($score >= 2.50) { $label = 'Satisfactory'; $colorClass = 'text-amber-700 bg-amber-50 border-amber-200 print:bg-amber-50'; }
                elseif ($score >= 1.50) { $label = 'Unsatisfactory'; $colorClass = 'text-orange-700 bg-orange-50 border-orange-200 print:bg-orange-50'; }
                else { $label = 'Poor'; $colorClass = 'text-red-700 bg-red-50 border-red-200 print:bg-red-50'; }
            @endphp

            <div class="border-2 p-5 rounded-2xl flex flex-col justify-center items-center text-center shadow-sm {{ $colorClass }} print:border-gray-300 print:shadow-none" style="-webkit-print-color-adjust: exact;">
                <p class="text-[9px] font-black uppercase tracking-widest mb-1 opacity-70">Institutional Rating</p>
                <h4 class="text-lg font-black leading-tight uppercase tracking-tighter">
                    {{ $label }}
                </h4>
                <p class="text-[10px] font-bold mt-1 opacity-80">Score: {{ number_format($score, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-8 shadow-sm">
            <table class="w-full text-left text-[10px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 font-black text-gray-400 uppercase">Range</th>
                        <th class="px-4 py-2 font-black text-gray-400 uppercase">Adjectival Rating</th>
                        <th class="px-4 py-2 font-black text-gray-400 uppercase">Standard Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr><td class="px-4 py-2 font-bold">4.50 – 5.00</td><td class="px-4 py-2 font-black text-emerald-600">OUTSTANDING</td><td class="px-4 py-2 text-gray-500 italic">Performance consistently exceeds all standards.</td></tr>
                    <tr><td class="px-4 py-2 font-bold">3.50 – 4.49</td><td class="px-4 py-2 font-black text-blue-600">VERY SATISFACTORY</td><td class="px-4 py-2 text-gray-500 italic">Performance consistently meets standards.</td></tr>
                    <tr><td class="px-4 py-2 font-bold">2.50 – 3.49</td><td class="px-4 py-2 font-black text-amber-600">SATISFACTORY</td><td class="px-4 py-2 text-gray-500 italic">Performance meets basic standards.</td></tr>
                    <tr><td class="px-4 py-2 font-bold">1.50 – 2.49</td><td class="px-4 py-2 font-black text-orange-600">UNSATISFACTORY</td><td class="px-4 py-2 text-gray-500 italic">Needs improvement in key areas.</td></tr>
                    <tr><td class="px-4 py-2 font-bold">1.00 – 1.49</td><td class="px-4 py-2 font-black text-red-600">POOR</td><td class="px-4 py-2 text-gray-500 italic">Performance is below acceptable standards.</td></tr>
                </tbody>
            </table>
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

                @if(!empty($breakdown[$type]['questions']) && is_array($breakdown[$type]['questions']))
                    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-6 page-break-avoid">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="px-6 py-3 text-[9px] font-black text-gray-400 uppercase">Criteria</th>
                                    <th class="px-6 py-3 text-[9px] font-black text-gray-400 uppercase text-right">Mean</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
    @foreach($breakdown[$type]['questions'] as $q)
                                    <tr>
                                        <td class="px-6 py-4 text-xs text-gray-700">
                                            <span class="font-bold uppercase tracking-tight mr-2">{{ $q['key'] }}:</span> 
                                            <span class="font-normal">{{ $q['text'] }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="font-black text-gray-900">{{ number_format($q['score'], 2) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
</tbody>
                        </table>
                    </div>
                    @if(!empty($breakdown[$type]['meta']['feedback']))
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 page-break-avoid">
                        @foreach($breakdown[$type]['meta']['feedback'] as $fb)
                            @php
                                $comment = $fb['comments'] ?? $fb['Comments'] ?? null;
                            @endphp
                            @if(!empty($fb['helped']) || !empty($fb['improved']) || !empty($comment))
                                <div class="p-5 border border-gray-200 rounded-xl bg-white">
                                    @if(!empty($fb['helped']))
                                        <p class="text-xs text-gray-600 mb-2"><strong class="text-green-600 uppercase text-[8px] block mb-1">Strength:</strong> "{{ $fb['helped'] }}"</p>
                                    @endif
                                    @if(!empty($fb['improved']))
                                        <p class="text-xs text-gray-600 mb-2"><strong class="text-amber-600 uppercase text-[8px] block mb-1">Growth Area:</strong> "{{ $fb['improved'] }}"</p>
                                    @endif
                                    @if(!empty($comment))
                                        <p class="text-xs text-gray-600"><strong class="text-indigo-600 uppercase text-[8px] block mb-1">Additional Comments:</strong> "{{ $comment }}"</p>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                     @endif
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
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('comparisonChart');
        if (!ctx) return;

        // Safely get data from Laravel - ensuring they are numbers
        const chartData = [
            {{ floatval($breakdown['student']['meta']['average'] ?? 0) }},
            {{ floatval($breakdown['peer']['meta']['average'] ?? 0) }},
            {{ floatval($breakdown['supervisor']['meta']['average'] ?? 0) }},
            {{ floatval($breakdown['self']['meta']['average'] ?? 0) }}
        ];

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['STUDENTS', 'PEERS', 'SUPERVISOR', 'SELF'],
                datasets: [{
                    data: chartData,
                    backgroundColor: ['#6366f1', '#10b981', '#f59e0b', '#374151'],
                    borderRadius: 6
                }]
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        max: 5,
                        grid: { display: false }
                    },
                    x: {
                        grid: { display: false }
                    }
                },
                plugins: { 
                    legend: { display: false } 
                }
            }
        });
    });

    function generateCompactPrint() {
    // 1. Capture data from Laravel
    const rawData = @json($breakdown);
    const teacherName = @json(auth()->user()->employee->first_name . ' ' . auth()->user()->employee->last_name);
    const academicYear = @json($academicYear->start_year . '-' . $academicYear->end_year);
    const semester = @json($semester);
    const overallScore = @json(number_format($overallScore, 2));

    if (!rawData) {
        alert("No evaluation data found.");
        return;
    }

    // 2. Determine Adjectival Rating based on Overall Score
    const scoreNum = parseFloat(overallScore);
    let institutionalRating = "";
    if (scoreNum >= 4.50) institutionalRating = "Outstanding";
    else if (scoreNum >= 3.50) institutionalRating = "Very Satisfactory";
    else if (scoreNum >= 2.50) institutionalRating = "Satisfactory";
    else if (scoreNum >= 1.50) institutionalRating = "Unsatisfactory";
    else institutionalRating = "Poor";

    const printWindow = window.open('', '_blank', 'width=950,height=1000');
    if (!printWindow) {
        alert("Please allow popups for this website.");
        return;
    }

    const categoriesOrder = ['self', 'peer', 'supervisor', 'student'];

    // 3. Start building the HTML document
    let htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Report - ${teacherName}</title>
            <style>
                @page { size: portrait; margin: 10mm; }
                body { font-family: 'Arial', sans-serif; font-size: 8pt; line-height: 1.2; color: #000; margin: 0; padding: 0; }
                .report-header { border-bottom: 2.5px solid #000; padding-bottom: 8px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: flex-end; }
                .teacher-info h1 { margin: 0; font-size: 14pt; text-transform: uppercase; font-weight: 900; }
                .score-display { text-align: right; }
                .score-value { font-size: 22pt; font-weight: 900; color: #4f46e5; }
                
                .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .summary-table th, .summary-table td { border: 1px solid #000; padding: 4px; text-align: center; }
                .summary-table th { background: #f9fafb; font-weight: 900; text-transform: uppercase; }
                
                .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
                .section { border: 0.75px solid #000; padding: 6px; break-inside: avoid; border-radius: 4px; }
                .section-header { background: #f9fafb; font-weight: 900; text-transform: uppercase; font-size: 7.5pt; padding: 3px 6px; border-bottom: 0.75px solid #000; margin-bottom: 6px; }
                
                .score-table { width: 100%; border-collapse: collapse; }
                .score-table td { padding: 2px 0; border-bottom: 0.5px solid #eee; font-size: 7pt; }
                
                .qual-title { text-transform: uppercase; font-size: 9pt; font-weight: 900; margin: 15px 0 6px 0; border-bottom: 1.5px solid #000; }
                .feedback-table { width: 100%; border-collapse: collapse; border: 0.75px solid #000; margin-bottom: 10px; }
                .feedback-table th, .feedback-table td { border: 0.75px solid #000; padding: 5px; font-size: 7pt; vertical-align: top; }
                th { background: #f9fafb; }
            </style>
        </head>
        <body>
            <div class="report-header">
                <div class="teacher-info">
                    <h1>Faculty Performance Report - ${teacherName}</h1>
                    <p>AY: ${academicYear} | ${semester} Semester</p>
                </div>
                <div class="score-display">
                    <div style="font-size: 9pt; font-weight: 900; text-transform: uppercase;">${institutionalRating}</div>
                    <div style="font-size: 7pt; font-weight: 900; color: #666;">INSTITUTIONAL MEAN</div>
                    <div class="score-value">${overallScore}</div>
                </div>
            </div>

            <table class="summary-table">
                <thead>
                    <tr>
                        ${categoriesOrder.map(type => `<th>${type.toUpperCase()} Mean</th>`).join('')}
                        <th style="background: #eee;">ADJECTIVAL RATING</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        ${categoriesOrder.map(type => `<td>${(rawData[type]?.meta?.average || 0).toFixed(2)}</td>`).join('')}
                        <td style="font-weight: 900;">${institutionalRating}</td>
                    </tr>
                </tbody>
            </table>

            <div class="grid">
    `;

    // Detailed Question Results
    categoriesOrder.forEach(type => {
        const cat = rawData[type];
        if (cat && cat.questions && cat.questions.length > 0) {
            htmlContent += `
                <div class="section">
                    <div class="section-header">${type} Detailed Result</div>
                    <table class="score-table">
                        ${cat.questions.map(q => `
                            <tr>
                                <td style="font-weight: bold; width: 25px;">${q.key}</td>
                                <td>${q.text}</td>
                                <td style="text-align: right; font-weight: bold;">${parseFloat(q.score).toFixed(2)}</td>
                            </tr>
                        `).join('')}
                    </table>
                </div>
            `;
        }
    });

    htmlContent += `</div>`;

    // Qualitative Feedback (Strengths, Growth, Comments)
    categoriesOrder.forEach(type => {
        const cat = rawData[type] || {};
        const feedbackRaw = cat.meta?.feedback || [];
        // Ensure data is an array (converts object to array if needed)
        const feedbackList = Array.isArray(feedbackRaw) ? feedbackRaw : Object.values(feedbackRaw);

        // Filter for entries that actually have content
        const validFeedback = feedbackList.filter(f => 
            (f.helped && f.helped !== '-') || 
            (f.improved && f.improved !== '-') || 
            (f.comments && f.comments !== '-') ||
            (f.Comments && f.Comments !== '-')
        );

        if (validFeedback.length > 0) {
            htmlContent += `
                <div class="qual-title">${type} Qualitative Feedback</div>
                <table class="feedback-table">
                    <thead>
                        <tr>
                            <th style="width: 33%;">Strengths</th>
                            <th style="width: 33%;">Growth Areas</th>
                            <th>Additional Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${validFeedback.map(f => {
                            // Map both possible comment keys
                            const finalComment = f.comments || f.Comments || '-';
                            return `
                            <tr>
                                <td>${f.helped || '-'}</td>
                                <td>${f.improved || '-'}</td>
                                <td>${finalComment}</td>
                            </tr>`;
                        }).join('')}
                    </tbody>
                </table>
            `;
        }
    });

    htmlContent += `
            <div style="margin-top: 30px; text-align: center; font-size: 7pt; color: #666; border-top: 1px solid #eee; padding-top: 10px;">
                Generated via Institutional 360-Degree Feedback System • ${new Date().toLocaleDateString()}
            </div>
        </body>
        </html>
    `;

    printWindow.document.write(htmlContent);
    printWindow.document.close();

    // Small delay to ensure styles render
    setTimeout(() => {
        printWindow.focus();
        printWindow.print();
    }, 500);
}
</script>
@endsection