<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // Add this line
use App\Http\Middleware\CheckRole;    // Add this line

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
    }
}