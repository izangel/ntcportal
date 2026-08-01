<div class="max-w-lg mx-auto py-10 px-4">
    @if($result === 'success')
        <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-8 text-center">
            <div class="mx-auto w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center">
                <i class="fas fa-check text-3xl text-emerald-600"></i>
            </div>
            <h1 class="mt-4 text-xl font-bold text-gray-900">Check-in Successful!</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $message }}</p>

            @if($block)
                <div class="mt-5 text-left bg-gray-50 rounded-xl border border-gray-100 p-4 space-y-1.5">
                    <p class="text-xs text-gray-500">Class</p>
                    <p class="text-sm font-semibold text-gray-800">{{ $block['course_code'] }} - {{ $block['course_name'] }}</p>
                    <div class="grid grid-cols-2 gap-2 mt-2">
                        <div>
                            <p class="text-[11px] text-gray-400">Schedule</p>
                            <p class="text-xs font-medium text-gray-700">{{ $block['schedule_string'] ?: '—' }}</p>
                        </div>
                        <div>
                            <p class="text-[11px] text-gray-400">Room</p>
                            <p class="text-xs font-medium text-gray-700">{{ $block['room_name'] ?: '—' }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-[11px] text-gray-400">Instructor</p>
                            <p class="text-xs font-medium text-gray-700">{{ $block['faculty_name'] }}</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-gray-400 pt-2">Checked in at: <span class="text-gray-700 font-medium">{{ \Carbon\Carbon::parse($checkedInAt)->format('h:i A') }}</span></p>
                </div>
            @endif

            <a href="{{ route('student.course-blocks') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-semibold rounded-lg hover:bg-indigo-700">
                <i class="fas fa-calendar-check mr-2"></i> View My Subjects
            </a>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-rose-200 p-8 text-center">
            <div class="mx-auto w-16 h-16 rounded-full bg-rose-100 flex items-center justify-center">
                <i class="fas fa-qrcode text-3xl text-rose-500"></i>
            </div>
            <h1 class="mt-4 text-xl font-bold text-gray-900">Check-in Unavailable</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $message }}</p>
            <a href="{{ route('dashboard') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-semibold rounded-lg hover:bg-gray-900">
                <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>
    @endif
</div>
