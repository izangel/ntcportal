<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        if (
            auth()->check() &&
            auth()->user()->force_password_change &&
            !$request->routeIs('password.change') &&
            !$request->routeIs('password.change.update')
        ) {
            return redirect()->route('password.change');
        }

        return $next($request);
    }

}
