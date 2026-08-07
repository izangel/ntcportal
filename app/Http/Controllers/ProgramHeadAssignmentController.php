<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\ProgramHead;
use App\Models\Employee;
use Illuminate\Http\Request;

class ProgramHeadAssignmentController extends Controller
{
    public function index()
    {
        $programs = Program::with(['programHeads' => function ($q) {
            $q->active()->with('employee');
        }])->orderBy('name')->get();

        $employees = Employee::query()
            ->whereNotNull('user_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get()
            ->mapWithKeys(fn ($e) => [
                $e->id => trim(($e->first_name ?? '') . ' ' . ($e->mid_name ? substr($e->mid_name, 0, 1) . '. ' : '') . ($e->last_name ?? ''))
                    . ($e->email ? " ({$e->email})" : ''),
            ])->sort();

        return view('admin.program-heads.index', compact('programs', 'employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'program_id' => 'required|exists:programs,id',
            'employee_id' => 'required|exists:employees,id',
        ]);

        ProgramHead::where('program_id', $request->program_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        ProgramHead::create([
            'program_id' => $request->program_id,
            'employee_id' => $request->employee_id,
            'is_active' => true,
        ]);

        $employee = Employee::find($request->employee_id);

        return back()->with('success', 'Program head assigned successfully.');
    }

    public function unassign(Program $program)
    {
        ProgramHead::where('program_id', $program->id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        return back()->with('success', "Program head removed for {$program->name}.");
    }
}