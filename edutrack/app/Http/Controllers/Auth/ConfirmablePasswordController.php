<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ConfirmablePasswordController extends Controller
{
    public function show(): Response
    {
        return Inertia::render('auth/ConfirmPassword');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Auth::guard('web')->validate([
            'email' => $request->user()->email,
            'password' => $request->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        $request->session()->put('auth.password_confirmed_at', time());

        $userType = $request->user()->user_type;

        $redirectRoute = match ($userType) {
            'prof' => 'prof.dashboard',
            'parent' => 'parent.dashboard',
            'student' => 'student.dashboard',
            default => '/',
        };

        return redirect()->intended(route($redirectRoute, absolute: false));
    }
}
