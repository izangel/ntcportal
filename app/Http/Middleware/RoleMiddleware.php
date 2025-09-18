<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Make sure this is imported

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        Log::info('RoleMiddleware: === START CHECK ===');
        Log::info('RoleMiddleware: Request URL: ' . $request->fullUrl());
        Log::info('RoleMiddleware: Auth Check: ' . (Auth::check() ? 'Authenticated' : 'NOT Authenticated'));

        if (!Auth::check()) {
            Log::warning('RoleMiddleware: User not authenticated. Redirecting to login.');
            return redirect('/login');
        }

        $user = Auth::user();

        $allowedRoles = [];
        foreach ($roles as $roleString) {
            $allowedRoles = array_merge($allowedRoles, explode('|', $roleString));
        }

       
        // --- DETAILED DEBUGGING LOGS ---
        Log::info('RoleMiddleware: Current User ID: ' . $user->id . ', Email: ' . $user->email);

        // This checks if the user has an employee relation
        Log::info('RoleMiddleware: User has employee relation: ' . ($user->employee ? 'TRUE' : 'FALSE'));

        // This checks the role directly from the employee model
        $employeeRole = $user->employee->role ?? 'NULL';
        Log::info('RoleMiddleware: Employee Role from DB (via user->employee->role): ' . $employeeRole);

        // This checks the result of the hasRole() method for the specific role being checked
        $hasAnyRequiredRole = collect($allowedRoles)->contains(function ($allowedRole) use ($user) {
            $hasRoleResult = $user->hasRole($allowedRole);
            Log::info('RoleMiddleware: Checking hasRole("' . $allowedRole . '"): ' . ($hasRoleResult ? 'TRUE' : 'FALSE'));
            return $hasRoleResult;
        });

        Log::info('RoleMiddleware: Final $hasAnyRequiredRole result: ' . ($hasAnyRequiredRole ? 'TRUE' : 'FALSE'));
        // --- END DETAILED DEBUGGING LOGS ---

        if (!$hasAnyRequiredRole) {
            Log::error('RoleMiddleware: User ' . $user->email . ' does not have any of the required roles. Required roles: ' . implode(', ', $allowedRoles));
            Log::info('RoleMiddleware: === END CHECK (403) ===');
            abort(403, 'Unauthorized access.');
        }

        Log::info('RoleMiddleware: User ' . $user->email . ' has required role. Proceeding.');
        Log::info('RoleMiddleware: === END CHECK (Allowed) ===');
        return $next($request);
    }
}