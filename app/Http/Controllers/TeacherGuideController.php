<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TeacherGuideController extends Controller
{
    public function show(Request $request)
    {
        return view('guides.teacher');
    }

    public function manual(Request $request)
    {
        return view('guides.teacher-manual');
    }
}