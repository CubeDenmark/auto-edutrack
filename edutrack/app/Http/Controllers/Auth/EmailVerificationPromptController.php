<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    public function __invoke(Request $request): RedirectResponse|Response
    {
        if ($request->user()->hasVerifiedEmail()) {
            $userType = $request->user()->user_type;

            $redirectRoute = match ($userType) {
                'prof' => 'prof.dashboard',
                'parent' => 'parent.dashboard',
                'student' => 'student.dashboard',
                default => '/',
            };

            return redirect()->intended(route($redirectRoute, absolute: false));
        }

        return Inertia::render('auth/VerifyEmail', [
            'status' => $request->session()->get('status'),
        ]);
    }
}
