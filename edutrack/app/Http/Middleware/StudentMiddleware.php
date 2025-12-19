<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class StudentMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if(Auth::check() && Auth::user()->user_type != "student") {
            abort(403, 'Unauthorized action.');
            return redirect(route('student.dashboard'));
        }
        return $next($request);
    }
    // public function handle(Request $request, Closure $next)
    // {
    //     if (Auth::check() && Auth::user()->user_type == "student") {
    //         return Inertia::render('Student/StudentDashboard');
    //     } else {
    //         return redirect('/');
    //     }
    //     // return $next($request);
    // }
}
