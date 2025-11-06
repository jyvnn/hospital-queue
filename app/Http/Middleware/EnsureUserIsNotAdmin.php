<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsNotAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        // If user is admin, redirect them to admin home
        if (Auth::user()->is_admin) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => 'Forbidden for admin users.'], 403);
            }
            return redirect()->route('home');
        }

        return $next($request);
    }
}
