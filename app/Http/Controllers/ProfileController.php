<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's personal information.
     */
    public function personalInformation(Request $request): View
    {
        $user = $request->user();
        $student = $user->student;
        $employee = $user->employee;
        
        return view('profile.personal-information', compact('user', 'student', 'employee'));
    }

    /**
     * Show the form for editing personal information.
     */
    public function editPersonalInformation(Request $request): View
    {
        $user = $request->user();
        $student = $user->student;
        $employee = $user->employee;
        
        return view('profile.edit-personal-information', compact('user', 'student', 'employee'));
    }

    /**
     * Update the user's personal information.
     */
    public function updatePersonalInformation(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        // Validate user fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // Update user
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        // Update student if exists
        if ($user->student) {
            $user->student->update([
                'first_name' => $validated['first_name'] ?? $user->student->first_name,
                'last_name' => $validated['last_name'] ?? $user->student->last_name,
                'middle_name' => $validated['middle_name'] ?? $user->student->middle_name,
                'date_of_birth' => $validated['date_of_birth'] ?? $user->student->date_of_birth,
            ]);
        }

        // Update employee if exists
        if ($user->employee) {
            $user->employee->update([
                'first_name' => $validated['first_name'] ?? $user->employee->first_name,
                'last_name' => $validated['last_name'] ?? $user->employee->last_name,
                'middle_name' => $validated['middle_name'] ?? $user->employee->middle_name,
                'phone' => $validated['phone'] ?? $user->employee->phone,
                'address' => $validated['address'] ?? $user->employee->address,
            ]);
        }

        return Redirect::route('profile.personal-information')->with('success', 'Personal information updated successfully!');
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
