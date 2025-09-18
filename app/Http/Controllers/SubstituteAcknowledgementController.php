<?php

namespace App\Http\Controllers;

use App\Models\LeaveApplicationClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException; // Used for throwing validation errors

class SubstituteAcknowledgementController extends Controller
{
    /**
     * Show the form/page for the substitute teacher to review and acknowledge.
     */
    public function showAcknowledgementForm(Request $request, LeaveApplicationClass $classId)
    {
        // Laravel's route model binding automatically finds the LeaveApplicationClass by ID.
        // The 'signed' middleware verifies the URL's integrity.

        // Eager load relationships needed for the view
        $classId->load(['leaveApplication.employee', 'substituteTeacher']);

        // If already acknowledged, show a different message
        if ($classId->sub_ack_at) { // Using the new shorter column name
            return view('substitute.already_acknowledged', ['class' => $classId]);
        }

        // Otherwise, show the form to acknowledge
        return view('substitute.acknowledge_form', ['class' => $classId]);
    }

    /**
     * Process the substitute teacher's acknowledgment.
     */
    public function processAcknowledgement(Request $request, LeaveApplicationClass $classId)
    {
        // Basic authentication check
        if (!Auth::check()) {
            throw ValidationException::withMessages([
                'auth' => 'You must be logged in to acknowledge this assignment.',
            ])->redirectToRoute('login'); // Redirect to login if not authenticated
        }

        // Ensure the logged-in user is the actual assigned substitute teacher for security
        if (!Auth::user()->employee || Auth::user()->employee->id !== $classId->substitute_teacher_id) {
            // If the user is logged in but not the assigned substitute, deny access
            abort(403, 'Unauthorized. You are not the assigned substitute teacher for this class.');
        }

        // Check if already acknowledged to prevent duplicate entries
        if ($classId->sub_ack_at) { // Using the new shorter column name
            return redirect()->route('dashboard')->with('info', 'This assignment has already been acknowledged.');
        }

        // Acknowledge the assignment
        $classId->sub_ack_at = Carbon::now(); // Record acknowledgment timestamp
        $classId->sub_ack_by = Auth::user()->employee->id; // Record who acknowledged
        $classId->save();

        // Mark the related notification as read (optional, but good practice)
        // Assuming the substitute teacher will see this notification in their dashboard
        if (Auth::user()->unreadNotifications->where('data.leave_application_class_id', $classId->id)->first()) {
            Auth::user()->unreadNotifications->where('data.leave_application_class_id', $classId->id)->first()->markAsRead();
        }

        return redirect()->route('dashboard')->with('success', 'Thank you for acknowledging the substitute assignment!');
    }
}