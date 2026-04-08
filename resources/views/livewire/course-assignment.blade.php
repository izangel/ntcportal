<div class="space-y-6">
    <div>
        <label>Academic Year</label>
        <select wire:model.live="selectedYear" class="w-full border rounded p-2">
            <option value="">-- Select Year --</option>
            @foreach($this->academicYears as $year)
                <option value="{{ $year->id }}">{{ $year->start_year }} - {{ $year->end_year }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Semester</label>
        <select wire:model.live="selectedSemester" class="w-full border rounded p-2" {{ empty($selectedYear) ? 'disabled' : '' }}>
            <option value="">-- Select Semester --</option>
            @foreach($this->semesters as $sem)
                <option value="{{ $sem->name }}">{{ $sem->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Section</label>
        <select wire:model.live="selectedSection" class="w-full border rounded p-2" {{ empty($selectedYear) ? 'disabled' : '' }}>
            <option value="">-- Select Section --</option>
            @foreach($this->sections as $section)
                <option value="{{ $section->id }}">{{ $section->name }}</option>
            @endforeach
        </select>
    </div>
    
    <div class="text-xs text-gray-400">
        Selected Year ID: {{ $selectedYear ?? 'None' }} | 
        Semesters Found: {{ $this->semesters->count() }}
    </div>
</div>