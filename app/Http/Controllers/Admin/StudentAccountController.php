<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StudentAccountController extends Controller
{
    public function index(Request $request)
    {
        // Start query for all users
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Simple search logic
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('email', 'asc')->paginate(15);

        return view('admin.student-accounts.index', compact('students'));
    }

    public function resetPassword(User $user)
    {
        // Security check: ensure we are only resetting a student
        if ($user->role !== 'student') {
            return back()->with('error', 'Unauthorized action.');
        }

        $user->update([
            'password' => Hash::make('northlink'),
        ]);

        return back()->with('success', "Password for {$user->email} has been reset to 'northlink'.");
    }

    public function edit(User $user)
    {
        return view('admin.student-accounts.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string'],
        ]);

        $user->update($validated);

        return redirect()->route('admin.student-accounts.index')
                         ->with('success', 'User account updated successfully.');
    }
}