@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto py-6">
    {{-- Breadcrumbs / Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Create New Advisory</h1>
            <p class="text-sm text-gray-500 mt-1">Draft and publish official institutional announcements.</p>
        </div>
        <a href="{{ route('admin.memos.index') }}"
            class="text-sm font-medium text-blue-600 hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>
    @if ($errors->any())
    <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-700">
        <strong>Validation Errors:</strong>
        <ul class="mt-2 list-disc pl-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    {{-- Role Authorization Check --}}
    @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('hr') || Auth::user()->hasRole('academic_head'))

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('admin.memos.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            {{-- Auto-generating Advisory No --}}
            <div>
                <label for="advisory_no" class="block text-sm font-semibold text-gray-700">Advisory No.</label>
                <input type="text" name="advisory_no" id="advisory_no" value="{{ old('advisory_no') }}"
                    placeholder="(e.g., ADV-XXXX)"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('advisory_no') border-red-300 @enderror" readonly>
                @error('advisory_no')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Subject Field --}}
            <div>
                <label for="subject" class="block text-sm font-semibold text-gray-700">Subject</label>
                <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                    placeholder="e.g., Suspended Classes due to Typhoon / Midterm Exam Reminders"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('subject') border-red-300 @enderror"
                    required>
                @error('subject')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                {{-- To Field --}}
                <div x-data="recipientSelector()" class="space-y-4">
                    <div>
                        <label for="to_groups" class="block text-sm font-semibold text-gray-700">Send To (Select multiple)</label>
                        
                        <!-- Hidden input to submit the selected groups array to Laravel backend -->
                        <template x-for="group in selectedGroups" :key="group">
                            <input type="hidden" name="to_groups[]" :value="group">
                        </template>

                        <!-- Custom Multi-Select Dropdown -->
                        <div class="relative mt-1">
                            <div @click="open = !open" 
                                 class="min-h-[38px] w-full rounded-lg border border-gray-300 bg-white p-1.5 shadow-sm focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 sm:text-sm flex flex-wrap gap-1.5 cursor-pointer items-center">
                                
                                <!-- Placeholder text if empty -->
                                <span x-show="selectedGroups.length === 0" class="text-gray-400 pl-1.5 select-none">Select target audience...</span>
                                
                                <!-- Selected Badges/Pills formatted as requested in Option 2 -->
                                <template x-for="group in selectedGroups" :key="group">
                                    <span class="inline-flex items-center gap-1 rounded bg-gray-800 px-2 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-700">
                                        <span x-text="formatLabel(group)"></span>
                                        <button type="button" @click.stop="toggleGroup(group)" class="text-gray-400 hover:text-gray-200 font-bold ml-1">&times;</button>
                                    </span>
                                </template>
                            </div>

                            <!-- Dropdown Options Menu -->
                            <div x-show="open" @click.outside="open = false" 
                                 class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none sm:text-sm" x-cloak>
                                <template x-for="option in options" :key="option.value">
                                    <div @click="toggleGroup(option.value)"
                                         :class="selectedGroups.includes(option.value) ? 'bg-blue-50 text-blue-900 font-semibold' : 'text-gray-900'"
                                         class="relative cursor-pointer select-none py-2 pl-3 pr-9 hover:bg-gray-100 flex items-center justify-between">
                                        <span x-text="option.label"></span>
                                        <span x-show="selectedGroups.includes(option.value)" class="text-blue-600">✓</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                        
                        @error('to_groups')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dynamic Specific Personnel Search Field -->
                    <div x-show="selectedGroups.includes('specific_personnel')" x-transition x-cloak>
                        <label for="specific_personnel_search" class="block text-sm font-semibold text-gray-700">Search & Select Personnel</label>
                        
                        <!-- Hidden input to submit selected employee IDs -->
                        <template x-for="id in selectedEmployees" :key="id">
                            <input type="hidden" name="specific_personnel[]" :value="id">
                        </template>

                        <!-- Personnel Search Dropdown -->
                        <div class="relative mt-1">
                            <input type="text" x-model="searchQuery" @input="searchEmployees" placeholder="Type employee name..."
                                   class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            
                            <!-- Employee Search Results -->
                            <div x-show="searchResults.length > 0" class="absolute z-20 mt-1 max-h-40 w-full overflow-auto rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 sm:text-sm">
                                <template x-for="emp in searchResults" :key="emp.id">
                                    <div @click="addEmployee(emp)" class="cursor-pointer py-2 pl-3 pr-9 hover:bg-gray-100 text-gray-900">
                                        <span x-text="emp.name"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Selected Employees Pills (Styled with dark badges) -->
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <template x-for="emp in selectedEmployeesDetails" :key="emp.id">
                                <span class="inline-flex items-center rounded-md bg-gray-800 px-2 py-1 text-xs font-medium text-gray-300 ring-1 ring-inset ring-gray-700">
                                    <span x-text="emp.name"></span>
                                    <button type="button" @click="removeEmployee(emp.id)" class="text-gray-400 hover:text-gray-200 font-bold ml-1">&times;</button>
                                </span>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- From Field --}}
                <div>
                    <label for="from" class="block text-sm font-semibold text-gray-700">From</label>

                    @php
                    $user = Auth::user();
                    $fullName = $user->name; // Default fallback

                    // If the user has an assigned employee_id, look up that unique employee record
                    if (!empty($user->employee_id)) {
                        $employeeData = \App\Models\Employee::find($user->employee_id);

                        if ($employeeData) {
                            $fullName = $employeeData->first_name . ' ' . $employeeData->last_name;
                        }
                    } else {
                        // Remainder hint if your account's employee_id column in the database is still NULL
                        $fullName = $user->name . ' (Please set employee_id in users table)';
                    }
                    @endphp

                    <input type="text" name="from" id="from" value="{{ old('from', $fullName) }}" readonly
                        class="mt-1 block w-full rounded-lg border-gray-300 bg-gray-50 text-gray-500 cursor-not-allowed shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('from') border-red-300 @enderror">

                    @error('from')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Date Field --}}
                <div>
                    <label for="date" class="block text-sm font-semibold text-gray-700">Date</label>
                    <input type="date" name="date" id="date" value="{{ old('date', date('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('date') border-red-300 @enderror"
                        required>
                    @error('date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Body Content --}}
            <div>
                <label for="body" class="block text-sm font-semibold text-gray-700">
                    Body (Main Content)
                </label>

                <textarea
                    name="body"
                    id="body"
                    rows="10"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm @error('body') border-red-300 @enderror"
                    >{{ old('body') }}</textarea>

                @error('body')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Publishing Action Row --}}
            <div class="bg-gray-50 -mx-6 -mb-6 p-4 flex items-center justify-end border-t border-gray-200">
                <div class="flex space-x-3">
                    
                    <button type="button" onclick="window.history.back()"
                        class="bg-white py-2 px-4 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none">
                        Cancel
                    </button>
                    <button type="submit"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Save Advisory
                        
                    </button>
                </div>
            </div>

        </form>
    </div>

    @else
    {{-- Unauthorized Access Message --}}
    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-times-circle text-red-400 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Access Denied</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p>Only the Administrator, HR, or Academic Head has permission to create and publish new advisories.</p>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
ClassicEditor
    .create(document.querySelector('#body'), {
        toolbar: [
            'heading', '|', 'bold', 'italic', 'underline', 'strikethrough', '|',
            'fontSize', 'fontColor', 'fontBackgroundColor', '|',
            'bulletedList', 'numberedList', '|', 'alignment', '|',
            'insertTable', 'blockQuote', 'undo', 'redo'
        ]
    })
    .catch(error => {
        console.error(error);
    });

// Alpine.js Option 2 Selector Logic
function recipientSelector() {
    return {
        open: false,
        selectedGroups: @json(old('to_groups', [])),
        options: [
            { value: 'all_students', label: 'To All Students' },
            { value: 'all_staff', label: 'To All Staff' },
            { value: 'all_shs_faculty', label: 'To All SHS Faculty' },
            { value: 'all_college_faculty', label: 'To All College Faculty' },
            { value: 'admin_personnel', label: 'To Admin Personnel' },
            { value: 'specific_personnel', label: 'Specific Personnel' }
        ],
        searchQuery: '',
        searchResults: [],
        selectedEmployees: @json(old('specific_personnel', [])),
        selectedEmployeesDetails: [],

        toggleGroup(value) {
            if (this.selectedGroups.includes(value)) {
                this.selectedGroups = this.selectedGroups.filter(g => g !== value);
            } else {
                this.selectedGroups.push(value);
            }
        },
        formatLabel(value) {
            return this.options.find(o => o.value === value)?.label || value;
        },
        async searchEmployees() {
            if (this.searchQuery.length < 2) {
                this.searchResults = [];
                return;
            }
            let response = await fetch(`/api/employees/search?q=${this.searchQuery}`);
            this.searchResults = await response.json();
        },
        addEmployee(emp) {
            if (!this.selectedEmployees.includes(emp.id)) {
                this.selectedEmployees.push(emp.id);
                this.selectedEmployeesDetails.push(emp);
            }
            this.searchQuery = '';
            this.searchResults = [];
        },
        removeEmployee(id) {
            this.selectedEmployees = this.selectedEmployees.filter(e => e !== id);
            this.selectedEmployeesDetails = this.selectedEmployeesDetails.filter(e => e.id !== id);
        }
    }
}
</script>
@endpush