<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, string ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // If trying to access login or register while authenticated
                if ($request->is('login') || $request->is('register')) {
                    return redirect()->route('courses.index');
                }
            }
        }

        return $next($request);
    }
}
