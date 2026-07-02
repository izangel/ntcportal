<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RoleAssignmentController extends Controller
{


 
public function index()


{

    $essential = [
    'registrar', 
    'program_head_shs', 
    'program_head_college', 
    'academic_head', 
    'faculty'
];

    // 1. Ensure core system roles always exist
   
    foreach ($essential as $name) {
        \App\Models\Role::firstOrCreate(['name' => $name]);
    }

    // 2. Fetch ALL roles for the checkboxes
    $roles = \App\Models\Role::orderBy('name')->get();

    // 3. Fetch Users, join employees, and sort by last_name
    $users = \App\Models\User::join('employees', 'users.id', '=', 'employees.user_id')
        ->select('users.*') // Ensure we get User fields, not just employee fields
        ->with(['employee', 'roles'])
        ->orderBy('employees.last_name', 'asc')
        ->get();

    return view('admin.roles.index', compact('users', 'roles'));
}

public function update(Request $request, \App\Models\User $user)
{
    // Use sync to overwrite old roles with the new selection from checkboxes
    // If no checkboxes are selected, it clears all roles (sync takes an empty array)
    $user->roles()->sync($request->roles ?? []);

    return back()->with('success', "Roles updated for {$user->name}");
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|unique:roles,name|max:255'
    ]);

    // Sanitize the name (lowercase and underscores)
    $name = strtolower(str_replace(' ', '_', $request->name));

    \App\Models\Role::create(['name' => $name]);

    return back()->with('success', 'New role created and available for assignment.');
}

    //
}
