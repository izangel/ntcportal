<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\AcademicYear;
use DB;

class PesDashboardSettings extends Component
{
    public $academic_year_id;
    public $semester;
    public $academicYears = [];

    public function mount()
    {
        $this->academicYears = AcademicYear::orderBy('start_year', 'desc')->get();
        
        // Fetch saved states from the database settings table
        $this->academic_year_id = DB::table('system_settings')->where('key', 'pes_dashboard_year_id')->value('value');
        $this->semester = DB::table('system_settings')->where('key', 'pes_dashboard_semester')->value('value') ?? '2nd';
    }

    public function saveSettings()
    {
        DB::table('system_settings')->where('key', 'pes_dashboard_year_id')->update(['value' => $this->academic_year_id, 'updated_at' => now()]);
        DB::table('system_settings')->where('key', 'pes_dashboard_semester')->update(['value' => $this->semester, 'updated_at' => now()]);

        session()->flash('message', 'PES Dashboard tracking target updated successfully!');
        
        // Redirect or refresh page to enforce the changes instantly
        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.admin.pes-dashboard-settings') ->extends('layouts.admin')
            ->section('content');
    }
}