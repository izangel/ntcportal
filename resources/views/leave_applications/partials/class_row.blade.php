{{-- resources/views/leave_applications/partials/class_row.blade.php --}}
@props(['index', 'class' => [], 'teachers'])

<div class="class-row grid grid-cols-6 gap-4 py-2 border-b border-gray-200 items-start">
    {{-- Hidden field for existing class ID (crucial for update logic) --}}
    @if(isset($class['id']))
        <input type="hidden" name="classes_data[{{ $index }}][id]" value="{{ $class['id'] }}">
    @endif

    {{-- Course Code --}}
    <div>
        <x-input id="classes_data_{{ $index }}_course_code" type="text" name="classes_data[{{ $index }}][course_code]" class="block mt-1 w-full" value="{{ $class['course_code'] ?? '' }}" />
        @error("classes_data.{$index}.course_code")
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- Title --}}
    <div>
        <x-input id="classes_data_{{ $index }}_title" type="text" name="classes_data[{{ $index }}][title]" class="block mt-1 w-full" value="{{ $class['title'] ?? '' }}" />
        @error("classes_data.{$index}.title")
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- Day/Time/Room --}}
    <div>
        <x-input id="classes_data_{{ $index }}_day_time_room" type="text" name="classes_data[{{ $index }}][day_time_room]" class="block mt-1 w-full" value="{{ $class['day_time_room'] ?? '' }}" />
        @error("classes_data.{$index}.day_time_room")
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- Topics to Discuss --}}
    <div>
        <textarea id="classes_data_{{ $index }}_topics" name="classes_data[{{ $index }}][topics]" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="1">{{ $class['topics'] ?? '' }}</textarea>
        @error("classes_data.{$index}.topics")
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

   
    
   

    {{-- Substitute Teacher Dropdown --}}
    <div>
        <select id="classes_data_{{ $index }}_substitute_teacher_id" name="classes_data[{{ $index }}][substitute_teacher_id]" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
            <option value="">-- Select Teacher --</option>

            
            @foreach($teachers as $teacher)
                <option value="{{ $teacher->id }}"
                    {{ (old("classes_data.{$index}.substitute_teacher_id", $class['substitute_teacher_id'] ?? '') == $teacher->id) ? 'selected' : '' }}>
                    {{ $teacher->name }}
                </option>
            @endforeach

        </select>
        @error("classes_data.{$index}.substitute_teacher_id")
            <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
        @enderror
    </div>

    {{-- Acknowledgment Status Display --}}
    <div>
        @if(isset($class['sub_ack_at']) && $class['sub_ack_at'])
            <span class="text-green-600 text-sm font-semibold">Acknowledged</span>
            <span class="text-xs text-gray-500 block">by {{ $class['acknowledged_by_name'] ?? 'Substitute' }}</span>
            <span class="text-xs text-gray-500 block">on {{ \Carbon\Carbon::parse($class['sub_ack_at'])->format('M d, Y') }}</span>
            {{-- Add a hidden input for the original teacher's note --}}
            <input type="hidden" name="classes_data[{{ $index }}][acknowledgement_signature]" value="{{ $class['acknowledgement_signature'] ?? '' }}">
            <span class="text-xs text-gray-500 block">Leaving teacher's note: {{ $class['acknowledgement_signature'] ?? 'N/A' }}</span>

            {{-- Javascript to disable fields if already acknowledged --}}
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const fieldsToDisable = [
                        'classes_data_{{ $index }}_course_code',
                        'classes_data_{{ $index }}_title',
                        'classes_data_{{ $index }}_day_time_room',
                        'classes_data_{{ $index }}_topics',
                        'classes_data_{{ $index }}_substitute_teacher_id'
                    ];
                    fieldsToDisable.forEach(id => {
                        const element = document.getElementById(id);
                        if (element) {
                            element.setAttribute('disabled', 'true');
                        }
                    });
                });
            </script>
        @else
            {{-- Input for leaving teacher's internal note/reference signature --}}
            <x-input id="classes_data_{{ $index }}_acknowledgement_signature" type="text" name="classes_data[{{ $index }}][acknowledgement_signature]" class="block mt-1 w-full" value="{{ $class['acknowledgement_signature'] ?? '' }}" placeholder="Leaving teacher's note/signature" />
            @error("classes_data.{$index}.acknowledgement_signature")
                <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
            @enderror
            <span class="text-red-500 text-sm block mt-1">Pending Acknowledgment</span>
        @endif
    </div>
</div>