<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // Add this line
use App\Http\Middleware\CheckRole;    // Add this line
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register your route middleware here
        Route::middleware('checkRole', CheckRole::class); // Add this line

        Gate::define('post-announcements', function (User $user) {
            // If employee is null, the whole thing becomes null (falsey)
            // If employee exists, it checks the role
            return in_array($user->employee?->role, ['admin', 'teacher']);
        });
    }
}