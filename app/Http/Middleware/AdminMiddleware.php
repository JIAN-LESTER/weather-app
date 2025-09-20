<?php

// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has admin role
        // Adjust this condition based on your user role system
        $user = Auth::user();
        
        // Option 1: If you have a 'role' column in users table
        if (!$user->role || $user->role !== 'admin') {
            abort(403, 'Access denied. Admin privileges required.');
        }

        // Option 2: If you have an 'is_admin' boolean column
        // if (!$user->is_admin) {
        //     abort(403, 'Access denied. Admin privileges required.');
        // }

        // Option 3: If you're using a more complex role system like Spatie Permission
        // if (!$user->hasRole('admin')) {
        //     abort(403, 'Access denied. Admin privileges required.');
        // }

        return $next($request);
    }
}