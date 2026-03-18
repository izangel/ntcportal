<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GenerateStudentAccountController extends Controller
{
    public function index()
    {
        return view('enrollment.generate-student-account');
    }
}
