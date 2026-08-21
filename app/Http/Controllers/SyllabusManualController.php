<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SyllabusManualController extends Controller
{
    public function show(Request $request)
    {
        return view('faculty.syllabus-help');
    }
}