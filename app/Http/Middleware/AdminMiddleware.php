<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ✅ If not logged in → redirect to login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // ✅ If logged in but not admin → show 403 error
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // ✅ If admin → allow access
        return $next($request);
    }
}
