@props(['employee' => null, 'unlinkedUsers' => []])

<div class="mb-4">
    <x-label for="last_name" value="{{ __('Employee Last Name') }}" />
    <x-input id="last_name" class="block mt-1 w-full" type="text" name="last_name" :value="old('last_name', $employee->last_name ?? '')" required autofocus autocomplete="last_name" />
    @error('last_name')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <x-label for="first_name" value="{{ __('Employee First Name') }}" />
    <x-input id="first_name" class="block mt-1 w-full" type="text" name="first_name" :value="old('first_name', $employee->first_name ?? '')" required autocomplete="first_name" />
    @error('first_name')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <x-label for="mid_name" value="{{ __('Employee Middle Name') }}" />
    <x-input id="mid_name" class="block mt-1 w-full" type="text" name="mid_name" :value="old('mid_name', $employee->mid_name ?? '')" required autocomplete="mid_name" />
    @error('mid_name')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <x-label for="email" value="{{ __('Employee Email (Optional)') }}" />
    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $employee->email ?? '')" autocomplete="email" />
    @error('email')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <x-label for="phone" value="{{ __('Phone (Optional)') }}" />
    <x-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $employee->phone ?? '')" autocomplete="tel" />
    @error('phone')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <x-label for="address" value="{{ __('Address (Optional)') }}" />
    <textarea id="address" name="address" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" rows="3">{{ old('address', $employee->address ?? '') }}</textarea>
    @error('address')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

<div class="mb-4">
    <x-label for="role" value="{{ __('Role') }}" />
    <select id="role" name="role" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" required>
        <option value="">-- Select Role --</option>
        <option value="teacher" {{ old('role', $employee->role ?? '') == 'teacher' ? 'selected' : '' }}>Teacher</option>
        <option value="staff" {{ old('role', $employee->role ?? '') == 'staff' ? 'selected' : '' }}>Staff</option>
        <option value="admin" {{ old('role', $employee->role ?? '') == 'admin' ? 'selected' : '' }}>Admin</option>
        <option value="hr" {{ old('role', $employee->role ?? '') == 'hr' ? 'selected' : '' }}>HR</option>
        <option value="academic_head" {{ old('role', $employee->role ?? '') == 'academic_head' ? 'selected' : '' }}>Academic Head</option>
    </select>
    @error('role')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>

{{-- Link Employee to User --}}
<div class="mb-4">
    <x-label for="user_id" value="{{ __('Link to User Account (Optional)') }}" />
    <select id="user_id" name="user_id" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
        <option value="">-- Do Not Link --</option>
        @foreach($unlinkedUsers as $user)
            <option value="{{ $user->id }}"
                {{ (old('user_id', $employee->user_id ?? '') == $user->id) ? 'selected' : '' }}>
                {{ $user->name }} ({{ $user->email }})
            </option>
        @endforeach
    </select>
    <p class="text-xs text-gray-500 mt-1">
        Only users who are not currently linked to an employee are shown here.
        If an employee is already linked, their current user will be selected.
    </p>
    @error('user_id')
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>