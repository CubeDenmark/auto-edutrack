<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

     public function handle(Request $request, Closure $next): Response
     {

         if(Auth::check() && Auth::user()->user_type != "prof") {
             abort(403, 'Unauthorized action.');
             return redirect(route('prof.dashboard'));
         }
         return $next($request);
     }
    // public function handle(Request $request, Closure $next)
    // {
    //     if (Auth::check() && Auth::user()->user_type == "prof") {
    //     //    return Inertia::render('Teacher/TeacherDashboard');
    //     return to_route('prof.dashboard');
    //     } else {
    //         return redirect('/');
    //     }
    //     // return $next($request);
    // }
}
