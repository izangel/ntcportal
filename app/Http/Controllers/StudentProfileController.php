<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StudentProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $student = $user->student ?? null; 

        // Confirms it serves from resources/views/profile/student.blade.php
// Change this line only if your file is named resources/views/profile/profile.blade.php
        return view('profile.profile', compact('user', 'student'));
    }
}