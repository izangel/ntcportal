<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; 
use App\Models\Acknowledgement; 
use App\Models\Employee;
use App\Models\MemoAdvisory;  
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AcknowledgementController extends Controller
{
    /**
     * Display a master list of all acknowledgments (Global Dashboard View).
     */
   public function index()
{
    // Simple fetch without needing heavy relationship chains for this view
    $acknowledgements = Acknowledgement::all(); 
    
    
    return view('admin.acknowledgements.index', compact('acknowledgements'));
}

public function store(Request $request, $advisory_no)
{
   

    $employee = Auth::user()->employee;

    $exists = Acknowledgement::where('employee_id', $employee->id)
        ->where('advisory_no', $advisory_no)
        ->exists();

    if ($exists) {
        return back()->with('info', 'You have already acknowledged this advisory.');
    }

    Acknowledgement::create([
        'employee_id' => $employee->id,
        'advisory_no' => $advisory_no, // This is now the advisory ID
        'acknowledged_at' => now(),
    ]);

    return back()->with('success', 'You have successfully submit the Acknowledgement. Thank you for your response! .');
}
    /**
     * Display acknowledgments for a single specific memo/advisory.
     * Accessible via a route like: /admin/acknowledgements/{advisory_no}
     */
 public function show(MemoAdvisory $advisory)
{
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

    /**
     * Remove an acknowledgment record if needed (e.g., reset an employee's status).
     */
    public function destroy($id)
    {
        $acknowledgement = Acknowledgement::findOrFail($id);
        $acknowledgement->delete();

        return redirect()->back()->with('success', 'Acknowledgment record removed successfully.');
    }
}