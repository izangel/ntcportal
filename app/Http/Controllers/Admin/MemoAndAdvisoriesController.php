<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; 
use App\Models\MemoAdvisory; 
use Illuminate\Http\Request;
use App\Models\Acknowledgement;
use Illuminate\Support\Facades\Auth;

class MemoAndAdvisoriesController extends Controller
{
    // Display a listing of the advisories
    public function index()
    {
       $user = auth()->user();

$advisories = MemoAdvisory::latest('date')
    ->get()
    ->filter(fn($advisory) => $advisory->canBeViewedBy($user))
    ->values();

        return view('admin.memos.index', compact('advisories'));
    }

    // Show the form for creating a new advisory
   public function create()
{
    if (
        !auth()->user()->hasRole('admin') &&
        !auth()->user()->hasRole('hr') &&
        !auth()->user()->hasRole('academic_head')
    ) {
        abort(403, 'Unauthorized action.');
    }

    auth()->user()->load('employee');

    return view('admin.memos.create');
}
    public function store(Request $request)
    {
        if (auth()->user()->hasRole('teacher')) {
            abort(403, 'Unauthorized action. restricted access.');
        }

        // 1. Updated Validation for Array Inputs
        $validated = $request->validate([
            'advisory_no'          => 'nullable|string|unique:memo_advisories,advisory_no',
            'to_groups'            => 'required|array|min:1',
            'to_groups.*'          => 'string|in:all_students,all_staff,all_shs_faculty,all_college_faculty,admin_personnel,specific_personnel',
            'specific_personnel'   => 'required_if:to_groups,specific_personnel|array',
            'specific_personnel.*' => 'exists:employees,id', 
            'from'                 => 'nullable|string|max:255', 
            'date'                 => 'required|date',
            'subject'              => 'required|string|max:255',
            'body'                 => 'required|string',
        ]);

        // 2. Prepare payload array from validated inputs
        $data = $validated;

        // 3. Generate Advisory Number if empty
        if (empty($data['advisory_no'])) {
            $latestId = MemoAdvisory::max('id') + 1;
            $data['advisory_no'] = 'ADV-' . str_pad($latestId, 4, '0', STR_PAD_LEFT);
        }

        // 4. Fallback for the 'From' field
        if (empty($data['from'])) {
            $user = auth()->user();
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();
            
            $data['from'] = $employee 
                ? $employee->first_name . ' ' . $employee->last_name 
                : $user->name;
        }

        // 5. Map arrays into database structure keys
        $data['to'] = $request->input('to_groups'); 
        $data['specific_personnel'] = $request->input('specific_personnel');

        // 6. Create a single Advisory Record cleanly (Let model array casting handle formatting)
MemoAdvisory::create($data);

return redirect()
    ->route('admin.memos.index')
    ->with('success', 'Advisory published and tracked successfully!');

        // 7. Handle Specific Personnel (Optional Mapping)
        if (in_array('specific_personnel', $data['to']) && !empty($request->specific_personnel)) {
            // Your mapping logic here...
        }

        return redirect()->route('admin.memos.index')->with('success', 'Advisory published and tracked successfully!');
    }

 public function show(MemoAdvisory $advisory)
{
    // Prevent unauthorized users from viewing this advisory
    if (!$advisory->canBeViewedBy(auth()->user())) {
        abort(403, 'You are not authorized to view this advisory.');
    }

    $advisory->load('acknowledgements.employee');

    $employee = Auth::user()->employee;

    $acknowledged = false;

    if ($employee) {
        $acknowledged = Acknowledgement::where('employee_id', $employee->id)
            ->where('advisory_no', $advisory->id)
            ->exists();
    }

    return view('admin.memos.show', compact(
        'advisory',
        'acknowledged'
    ));
}

    // Remove the specified advisory from storage
  public function destroy($id)
{
    // Block both teachers and staff from deleting backend records
    if (auth()->user()->hasRole('teacher') || auth()->user()->hasRole('staff')) {
        abort(403, 'Unauthorized action. Restricted access.');
    }
    
    $advisory = MemoAdvisory::findOrFail($id);
    $advisory->delete();
        
    return redirect()->route('admin.memos.index')->with('success', 'Advisory deleted successfully.');
}
    
}