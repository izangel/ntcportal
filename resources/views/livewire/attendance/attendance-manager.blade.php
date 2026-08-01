<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">QR Code Attendance</h1>
        <p class="text-sm text-gray-600">Select a class, show the QR code, and let students scan to check in. You can also mark attendance manually.</p>
    </div>

    @if (session()->has('message'))
        <div class="mb-4 p-4 text-sm text-emerald-800 bg-emerald-100 rounded-lg border border-emerald-200">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-4 text-sm text-rose-800 bg-rose-100 rounded-lg border border-rose-200">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">School Year</label>
                <select wire:model.live="academicYearId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}">{{ $academicYear->start_year }} - {{ $academicYear->end_year }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Semester</label>
                <select wire:model.live="semester" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($semesterOptions as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Class / Course Block</label>
                <select wire:model.live="selectedBlockId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">-- Choose a class --</option>
                    @foreach($assignedBlocks as $block)
                        <option value="{{ $block['id'] }}">
                            {{ $block['course_code'] }} - {{ $block['course_name'] }} ({{ $block['sections'] }}) {{ $block['schedule_string'] }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button wire:click="startSession" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700 focus:outline-none">
                <i class="fas fa-qrcode mr-2"></i> Show QR Code
            </button>

            @if($selectedBlockId)
                <span class="text-xs text-gray-500">
                    <i class="fas fa-users mr-1"></i> {{ $assignedBlocks ? collect($assignedBlocks)->firstWhere('id', $selectedBlockId)['student_count'] ?? '-' : '-' }} students enrolled
                </span>
            @endif
        </div>
    </div>

    @if($token && $qrDataUri)
        <div wire:poll.15s="tick" class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        Student QR Check-in
                    </h2>
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-700">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse mr-1"></span>
                        {{ $summary['present'] ?? 0 }} / {{ $summary['total'] ?? 0 }} checked in
                    </span>
                </div>

                <div class="flex justify-center">
                    <img src="{{ $qrDataUri }}" alt="Attendance QR Code" class="w-64 h-64 rounded-lg border border-gray-200 shadow-sm">
                </div>

                <div class="mt-4 text-center" x-data="{
                    expiresAt: '{{ $tokenExpiresAt }}',
                    get remaining() {
                        const ms = new Date(this.expiresAt).getTime() - Date.now();
                        return ms > 0 ? Math.ceil(ms / 1000) : 0;
                    },
                    timer: null,
                    init() {
                        this.timer = setInterval(() => {
                            if (this.remaining <= 0) {
                                clearInterval(this.timer);
                            }
                        }, 1000);
                    }
                }">
                    <p class="text-xs text-gray-500">
                        This QR refreshes every 90 seconds and only works for students enrolled in this class.
                    </p>
                    <div class="mt-2 text-3xl font-bold text-indigo-600" x-text="`0:${String(Math.max(remaining,0)).padStart(2,'0')}`"></div>
                    <button wire:click="regenerateQr" class="mt-3 inline-flex items-center px-3 py-1.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-200">
                        <i class="fas fa-rotate mr-1.5"></i> Refresh now
                    </button>
                </div>

                <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-100">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Check-in link (in case scanning fails)</label>
                    <div class="flex items-center gap-2">
                        <input readonly value="{{ $qrUrl }}" class="flex-1 text-[11px] text-gray-600 bg-white rounded-md border border-gray-200 px-2 py-1.5 truncate" onclick="this.select()">
                        <button onclick="navigator.clipboard.writeText('{{ $qrUrl }}').then(() => { this.textContent = 'Copied'; setTimeout(() => this.textContent = 'Copy', 1500); })" class="shrink-0 px-3 py-1.5 bg-indigo-50 text-indigo-700 text-xs font-semibold rounded-lg hover:bg-indigo-100">
                            Copy
                        </button>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <h2 class="text-sm font-bold text-gray-800 uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span>
                        Attendance Roster
                    </h2>

                    <div class="flex flex-wrap items-center gap-2">
                        <button wire:click="exportCsv" class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-lg hover:bg-emerald-100">
                            <i class="fas fa-file-csv mr-1.5"></i> Export CSV
                        </button>
                        <button wire:click="printRoster" class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-700 text-xs font-semibold rounded-lg hover:bg-gray-100">
                            <i class="fas fa-print mr-1.5"></i> Print / PDF
                        </button>
                        <input type="date" wire:model.live="attendanceDate" wire:change="loadRoster" class="text-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Present: {{ $summary['present'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Late: {{ $summary['late'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">Absent: {{ $summary['absent'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-700">Excused: {{ $summary['excused'] ?? 0 }}</span>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500">No record: {{ ($summary['total'] ?? 0) - (($summary['present'] ?? 0) + ($summary['late'] ?? 0) + ($summary['absent'] ?? 0) + ($summary['excused'] ?? 0)) }}</span>
                </div>

                @if(count($roster) === 0)
                    <p class="text-sm text-gray-400 italic py-8 text-center">No students enrolled in this class.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Student</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">ID Number</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Checked In At</th>
                                    <th class="px-4 py-2.5 text-left text-[11px] font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($roster as $index => $student)
                                    <tr>
                                        <td class="px-4 py-2 text-xs text-gray-500">{{ $index + 1 }}</td>
                                        <td class="px-4 py-2 text-xs font-medium text-gray-800">{{ $student['name'] }}</td>
                                        <td class="px-4 py-2 text-xs text-gray-600">{{ $student['student_number'] }}</td>
                                        <td class="px-4 py-2">
                                            @if($student['status'])
                                                @php
                                                    $badge = match($student['status']) {
                                                        'present' => 'bg-emerald-100 text-emerald-800',
                                                        'late' => 'bg-amber-100 text-amber-800',
                                                        'absent' => 'bg-rose-100 text-rose-800',
                                                        'excused' => 'bg-gray-100 text-gray-700',
                                                        default => 'bg-gray-100 text-gray-700',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase {{ $badge }}">{{ $student['status'] }}</span>
                                            @else
                                                <span class="text-[11px] text-gray-400 italic">Not recorded</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs text-gray-600">
                                            {{ $student['checked_in_at'] ? \Carbon\Carbon::parse($student['checked_in_at'])->format('h:i A') : '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="flex items-center gap-1">
                                                @php
                                                    $buttonClass = match($student['status']) {
                                                        'present' => 'bg-emerald-600',
                                                        'late' => 'bg-amber-500',
                                                        'absent' => 'bg-rose-600',
                                                        'excused' => 'bg-gray-500',
                                                        default => 'bg-gray-200 text-gray-500 hover:bg-gray-300',
                                                    };
                                                @endphp
                                                <button wire:click="markStatus({{ $student['student_id'] }}, 'present')" class="px-2 py-1 text-[10px] font-bold rounded-md {{ $student['status'] === 'present' ? 'bg-emerald-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-emerald-600 hover:text-white' }}">P</button>
                                                <button wire:click="markStatus({{ $student['student_id'] }}, 'late')" class="px-2 py-1 text-[10px] font-bold rounded-md {{ $student['status'] === 'late' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-amber-500 hover:text-white' }}">L</button>
                                                <button wire:click="markStatus({{ $student['student_id'] }}, 'absent')" class="px-2 py-1 text-[10px] font-bold rounded-md {{ $student['status'] === 'absent' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-rose-600 hover:text-white' }}">A</button>
                                                <button wire:click="markStatus({{ $student['student_id'] }}, 'excused')" class="px-2 py-1 text-[10px] font-bold rounded-md {{ $student['status'] === 'excused' ? 'bg-gray-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-500 hover:text-white' }}">E</button>
                                                @if($student['status'])
                                                    <button wire:click="clearStatus({{ $student['student_id'] }})" title="Clear status" class="px-1.5 py-1 text-[10px] font-bold rounded-md bg-gray-100 text-gray-400 hover:bg-rose-100 hover:text-rose-600">
                                                        <i class="fas fa-xmark"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @elseif($selectedBlockId && !$token)
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-10 text-center">
            <i class="fas fa-qrcode text-4xl text-gray-300 mb-3"></i>
            <p class="text-sm text-gray-500">Click <span class="font-semibold text-indigo-600">Show QR Code</span> to start an attendance session for this class.</p>
        </div>
    @endif
</div>
