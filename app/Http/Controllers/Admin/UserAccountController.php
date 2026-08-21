<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAccountController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'all'); // all | orphans | students-without-account

        $usersQuery = User::with(['employee', 'student']);

        if ($request->filled('search')) {
            $search = $request->search;
            $usersQuery->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->orderBy('email')->paginate(25);
        $users->appends($request->all());

        // Accounts with no employee and no student profile attached.
        $orphans = User::doesntHave('employee')
            ->doesntHave('student')
            ->orderBy('email')
            ->paginate(25, ['*'], 'orphans_page');
        $orphans->appends($request->all());

        $studentsWithoutAccount = Student::with(['sections.program', 'user'])
            ->whereNull('user_id')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(25, ['*'], 'swa_page');
        $studentsWithoutAccount->appends($request->all());

        $counts = [
            'all' => User::count(),
            'orphans' => User::doesntHave('employee')->doesntHave('student')->count(),
            'students-without-account' => Student::whereNull('user_id')->count(),
        ];

        return view('admin.user-accounts.index', compact('tab', 'users', 'orphans', 'studentsWithoutAccount', 'counts'));
    }

    public function destroy(User $user)
    {
        if ($user->employee || $user->student) {
            return back()->with('error', "Cannot delete {$user->email} — it is linked to an employee or student record.");
        }

        $email = $user->email;
        $user->delete();

        return back()->with('success', "Deleted unlinked user account: {$email}.");
    }

    public function createStudentAccount(Student $student)
    {
        if ($student->user_id) {
            return back()->with('error', "{$student->first_name} {$student->last_name} already has a user account.");
        }

        $email = $this->uniqueEmail($this->buildEmail($student));

        $user = User::create([
            'name' => trim(trim($student->first_name . ' ' . $student->middle_name) . ' ' . $student->last_name),
            'email' => $email,
            'password' => Hash::make('northlink'),
            'role' => 'student',
        ]);

        $student->update([
            'user_id' => $user->id,
            'email' => $email,
        ]);

        return back()->with('success', "Created account for {$user->name}: <strong>{$user->email}</strong> (default password: <code>northlink</code>).");
    }

    private function buildEmail(Student $student)
    {
        $studentEmail = strtolower(trim((string) $student->email));

        if ($studentEmail !== '') {
            return $studentEmail;
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $student->last_name . $student->first_name));

        return ($slug !== '' ? $slug : 'student') . '@northlink.edu.ph';
    }

    private function uniqueEmail($baseEmail)
    {
        if (!str_contains($baseEmail, '@')) {
            return $baseEmail;
        }

        [$prefix, $domain] = explode('@', $baseEmail, 2);
        $email = $baseEmail;
        $i = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$prefix}{$i}@{$domain}";
            $i++;
        }

        return $email;
    }
}