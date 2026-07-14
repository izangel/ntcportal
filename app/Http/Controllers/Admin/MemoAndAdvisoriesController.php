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

        $advisories = MemoAdvisory::with('recipients')
            ->latest('date')
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

        // STEP 1: Bulletproof preparation for form payloads that may arrive as arrays or comma-separated strings
        $rawPersonnel = $request->input('specific_personnel');
        $rawTargets = $request->input('to_groups', []);

        $normalizedPersonnel = [];

        if (is_array($rawPersonnel)) {
            $normalizedPersonnel = array_values(array_filter(array_map(function ($value) {
                if (is_int($value) || is_numeric($value)) {
                    return (int) $value;
                }
                return is_string($value) && trim($value) !== '' ? trim($value) : null;
            }, $rawPersonnel), fn ($value) => $value !== null));
        } elseif (is_string($rawPersonnel) && trim($rawPersonnel) !== '') {
            $normalizedPersonnel = array_values(array_filter(array_map(function ($value) {
                $trimmed = trim($value);
                if ($trimmed === '') {
                    return null;
                }
                return is_numeric($trimmed) ? (int) $trimmed : $trimmed;
            }, explode(',', $rawPersonnel)), fn ($value) => $value !== null));
        }

        $request->merge(['specific_personnel' => $normalizedPersonnel]);

        if (is_string($rawTargets) && trim($rawTargets) !== '') {
            $request->merge(['to_groups' => array_values(array_filter(array_map('trim', explode(',', $rawTargets)), fn ($value) => $value !== ''))]);
        } elseif (!is_array($rawTargets)) {
            $request->merge(['to_groups' => []]);
        }

        // 2. Updated Validation for Array Inputs (Now parses safely)
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

      

        // 3. Prepare payload array from validated inputs
        $data = $validated;

        // 4. Generate Advisory Number if empty
        if (empty($data['advisory_no'])) {
            $latestId = MemoAdvisory::max('id') + 1;
            $data['advisory_no'] = 'ADV-' . str_pad($latestId, 4, '0', STR_PAD_LEFT);
        }

        // 5. Fallback for the 'From' field
        if (empty($data['from'])) {
            $user = auth()->user();
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();
            
            $data['from'] = $employee 
                ? $employee->first_name . ' ' . $employee->last_name 
                : $user->name;
        }

        // 5. Map arrays into database structure keys
        $data['to'] = array_values(array_unique(array_filter($request->input('to_groups', []), fn ($value) => is_string($value) && trim($value) !== '')));

        // Ensure we explicitly pull the modified array we merged into the request in Step 1
        $cleanPersonnel = array_values(array_unique(array_filter(array_map(function ($value) {
            if (is_int($value) || is_numeric($value)) {
                return (int) $value;
            }
            return is_string($value) && trim($value) !== '' ? trim($value) : null;
        }, $request->input('specific_personnel', [])), fn ($value) => $value !== null)));

        if (in_array('specific_personnel', $data['to']) && empty($cleanPersonnel)) {
            $cleanPersonnel = [];
        }

        // FIX: Assign raw clean array elements to the data tracking key.
        // Let Eloquent model casts or text database engines handle assignments natively.
        $data['specific_personnel'] = $cleanPersonnel;

        // 6. Create the advisory base record
        $memo = MemoAdvisory::create($data);

        // 7. Save specific personnel dependencies to your pivot table
        if (
            in_array('specific_personnel', $data['to']) &&
            !empty($cleanPersonnel)
        ) {
            // PASS THE NATIVE INTEGER ARRAY ($cleanPersonnel) INTO SYNC()
            // This connects to 'advisory_recipients' via 'advisory_id' and 'employee_id'
            $memo->recipients()->sync($cleanPersonnel);
        }

        // Redirect
        return redirect()
            ->route('admin.memos.index')
            ->with('success', 'Advisory published and tracked successfully!');
    }

    public function show(MemoAdvisory $advisory)
    {
        // Prevent unauthorized users from viewing this advisory
        if (!$advisory->canBeViewedBy(auth()->user())) {
            abort(403, 'You are not authorized to view this advisory.');
        }

        $advisory->load(['acknowledgements.employee', 'recipients']);

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