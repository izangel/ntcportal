<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class BulkUserController extends Controller
{
    public function index() {
        return view('admin.users.bulk-upload');
    }

    public function store(Request $request) {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip the header row
        fgetcsv($handle);

        while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
            // Data indices: 0 => id, 1 => email, 2 => role, 3 => password
            User::updateOrCreate(
                ['email' => $data[0]], // Unique identifier
                [
                   
                    'role' => $data[1] ?? 'student',
                    'password' => Hash::make($data[2] ?? 'northlink'),
                ]
            );
        }

        fclose($handle);
        return back()->with('success', 'Users uploaded successfully!');
    }
}