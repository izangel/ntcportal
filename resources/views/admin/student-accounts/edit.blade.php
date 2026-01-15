@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-8 px-4">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Edit User Account</h1>
        <p class="text-sm text-gray-500">Update email address and role permissions.</p>
    </div>

    <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-6">
        <form action="{{ route('admin.student-accounts.update', $user->id) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')
            
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                    class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                <select name="role" id="role"
                        class="w-full rounded-xl border-gray-200 text-sm focus:ring-indigo-500 focus:border-indigo-500 shadow-sm">
                    <option value="student" {{ (old('role', $user->role) == 'student') ? 'selected' : '' }}>Student</option>
                    <option value="teacher" {{ (old('role', $user->role) == 'teacher') ? 'selected' : '' }}>Teacher</option>
                    <option value="admin" {{ (old('role', $user->role) == 'admin') ? 'selected' : '' }}>Admin</option>
                    <option value="hr" {{ (old('role', $user->role) == 'hr') ? 'selected' : '' }}>HR</option>
                    <option value="academic_head" {{ (old('role', $user->role) == 'academic_head') ? 'selected' : '' }}>Academic Head</option>
                </select>
            </div>
            
            <div class="flex items-center justify-end gap-4 pt-4">
                <a href="{{ route('admin.student-accounts.index') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-xl text-sm font-medium hover:bg-indigo-700 transition shadow-sm shadow-indigo-200">
                    Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection