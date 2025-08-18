<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Only apply to users, not admins
        if ($user && $user->role === 'user' && $user->force_password_change) {
            // Avoid redirect loop
            if (!$request->is('force-change-password')) {
                return redirect()->route('force.change.password');
            }
        }

        return $next($request);
    }
}
