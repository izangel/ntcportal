<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User; // Import the User model
use Illuminate\Http\Request;
use Illuminate\Validation\Rule; // Import Rule for unique validation

class EmployeeController extends Controller
{
    public function __construct()
    {
        // Apply middleware (e.g., auth for all, and a role middleware if you have one)
        $this->middleware('auth');
        // Example: $this->middleware('can:manage-employees'); // If you have Gates/Policies
    }

    /**
     * Display a listing of the employees.
     */
    public function index()
    {
        $employees = Employee::with('user')->orderBy('name')->paginate(10);
        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new employee.
     */
    public function create()
    {
        // Get users who are not yet linked to an employee
        $unlinkedUsers = User::doesntHave('employee')->get();
        return view('employees.create', compact('unlinkedUsers'));
    }

    /**
     * Store a newly created employee in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'mid_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:employees,email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'role' => 'required|string|in:teacher,staff,admin,hr,academic_head', // Adjust roles as per your app
            'user_id' => 'nullable|exists:users,id|unique:users,employee_id', // Must be an existing user, and that user shouldn't already have an employee_id
        ]);

       
        $employee = Employee::create($validatedData);

        // Link employee to user if user_id is provided
        // if ($request->filled('user_id')) {
        //     User::where('id', $validatedData['user_id'])->update(['employee_id' => $employee->id]);
        // }

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    /**
     * Display the specified employee.
     */
    public function show(Employee $employee)
    {
        $employee->load('user'); // Load the associated user
        return view('employees.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified employee.
     */
    public function edit(Employee $employee)
    {
        // Get users who are not yet linked OR who are currently linked to THIS employee
        $unlinkedUsers = User::doesntHave('employee')
                             ->orWhere('id', $employee->user_id)
                             ->get();

        return view('employees.edit', compact('employee', 'unlinkedUsers'));
    }

    /**
     * Update the specified employee in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $validatedData = $request->validate([
            'last_name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'mid_name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('employees', 'email')->ignore($employee->id), // Ignore current employee's email
                'max:255',
            ],
           
            'role' => 'required|string|in:teacher,staff,admin,hr,academic_head',
            // 'user_id' => [
            //     'nullable',
            //     'exists:users,id',
            //     Rule::unique('users', 'employee_id')->ignore($employee->user_id ?? null, 'id'), // Ignore current linked user's employee_id
            // ],

            'user_id' => [
                'nullable',
                'exists:users,id',
                Rule::unique('employees', 'user_id')->ignore($employee->id),
            ],
           
        ]);
        
       

        // Unlink previous user if a new user is selected or if user_id is intentionally nullified
        if ($employee->user && ($request->user_id == null || $request->user_id != $employee->user->id)) {
            $employee->user->update(['employee_id' => null]);
        }

        
        $employee->update($validatedData);

        // Link new user if user_id is provided
        if ($request->filled('user_id')) {
            User::where('id', $validatedData['user_id'])->update(['employee_id' => $employee->id]);
        }

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    /**
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee)
    {
        // Unlink any associated user before deleting the employee
        if ($employee->user) {
            $employee->user->update(['employee_id' => null]);
        }

        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }
}