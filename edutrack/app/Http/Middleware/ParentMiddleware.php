<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ParentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(Auth::check() && Auth::user()->user_type != "parent") {
            abort(403, 'Unauthorized action.');
            return redirect(route('parent.dashboard'));
        }
        return $next($request);
    }
    // public function handle(Request $request, Closure $next)
    // {

    //     if (Auth::check() && Auth::user()->user_type == "parent") {
    //         return Inertia::render('Parent/ParentDashboard');
    //     } else {
    //         return redirect('/');
    //     }
    //     // return $next($request);
    // }
}
