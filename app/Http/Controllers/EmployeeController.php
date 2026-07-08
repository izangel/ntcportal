<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Employee::with('user');

        // Filter by Keyword
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('last_name', 'like', '%' . $request->search . '%')
                ->orWhere('first_name', 'like', '%' . $request->search . '%')
                ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $employees = $query->orderBy('last_name')->paginate(10);
        
        // Pass roles array to view for dropdown population
        $roles = ['teacher', 'staff', 'admin', 'hr', 'academic_head'];

        return view('employees.index', compact('employees', 'roles'));
    }

    public function create()
    {
        // Fetch only non-student users who are not yet linked to an employee profile
      $unlinkedUsers = User::doesntHave('employee')
        ->doesntHave('student')
        ->orderBy('email')
        ->get();
        return view('employees.create', compact('unlinkedUsers'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'mid_name' => 'nullable|string|max:255',
            'email'       => 'nullable|email|unique:employees,email|max:255',
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:255',
            'role'        => 'required|string|in:teacher,staff,admin,hr,academic_head',
            'user_id'     => 'nullable|exists:users,id|unique:employees,user_id', // Enforce unique mapping constraints safely
        ]);

        Employee::create($validatedData);

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $employee->load('user');
        return view('employees.show', compact('employee'));
    }

    public function resetPassword(Employee $employee)
    {
        if (!$employee->user) {
            return redirect()->route('employees.index')->with('error', 'Cannot reset password: Employee is not linked to a user account.');
        }

        $newPassword = Str::random(12);

        $employee->user->update([
            'password' => Hash::make($newPassword),
        ]);

        // Swapped to markdown-safe HTML parsing using standard line-breaks for clean presentation
        return redirect()->route('employees.index')->with(
            'password_success',
            "Password for <strong>{$employee->user->email}</strong> has been reset to: <code class='bg-gray-100 p-1 rounded font-bold text-red-600'>{$newPassword}</code>. Please copy and share this securely right now."
        );
    }

    public function edit(Employee $employee)
    {
        // Load users without a linked employee profile, but preserve the current employee's link
        $unlinkedUsers = User::where('role', '!=', 'student')
            ->where(function ($query) use ($employee) {
                $query->doesntHave('employee')
                      ->orWhere('id', $employee->user_id);
            })
            ->orderBy('email')
            ->get();

        return view('employees.edit', compact('employee', 'unlinkedUsers'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'last_name'   => 'required|string|max:255',
            'first_name'  => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'email'       => [
                'nullable',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id),
                'max:255',
            ],
            'phone'       => 'nullable|string|max:50',
            'address'     => 'nullable|string|max:255',
            'role'        => 'required|string|in:teacher,staff,admin,hr,academic_head',
            'user_id'     => [
                'nullable',
                'exists:users,id',
                Rule::unique('employees', 'user_id')->ignore($employee->id), // Correct structural table targets
            ],
        ]);

        $employee->update($validatedData);

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        // Eloquent takes care of unlinking implicitly if user_id is dropped on deletion
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    public function archive()
    {
        // Retrieve ONLY soft-deleted models
        $employees = Employee::onlyTrashed()->orderBy('last_name')->paginate(10);
        return view('employees.archive', compact('employees'));
    }

    public function restore($id)
    {
        $employee = Employee::onlyTrashed()->findOrFail($id);
        $employee->restore();

        return redirect()->route('employees.index')->with('success', "Profile for {$employee->first_name} has been successfully restored.");
    }
public function search(Request $request)
{
    
    $q = trim($request->get('q'));

    if (empty($q)) {
        return response()->json([]);
    }

    $employees = Employee::where(function ($query) use ($q) {
            $query->where('first_name', 'LIKE', "%{$q}%")
                  ->orWhere('last_name', 'LIKE', "%{$q}%");
        })
        ->orderBy('last_name')
        ->limit(10)
        ->get([
            'id',
            'first_name',
            'last_name',
        ]);

    return response()->json($employees);
}
}