<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        // Apply only to users (not admins)
        if ($user && $user->role === 'user' && $user->force_password_change) {
            if (!$request->is('force-change-password') && !$request->is('force-change-password/*')) {
                return redirect()->route('force.change.password');
            }
        }

        return $next($request);
    }
}
