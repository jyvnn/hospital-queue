<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        // If the user is authenticated
        if (Auth::check()) {
            // Allow admins
            if (Auth::user()->is_admin) {
                return $next($request);
            }

            // Authenticated but not an admin: return JSON for API/Ajax, otherwise redirect to user dashboard
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }

            return redirect()->route('user.dashboard')->with('error', 'You are not authorized to access that page.');
        }

        // Not authenticated: return JSON 401 for API/Ajax, otherwise redirect to login page
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->route('login');
    }
}
