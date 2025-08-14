<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, String $midlewareRole): Response
    {   
        // admin => user
        if (Auth::user()->role != $midlewareRole) {
            // return redirect(route(Auth::user()->role.".dashboard"));
            if($midlewareRole == "user"){
                return redirect(route("admin.dashboard"));
            }else{
                return redirect(route("user.dashboard"));
            }
        }
        return $next($request);
    }
}
