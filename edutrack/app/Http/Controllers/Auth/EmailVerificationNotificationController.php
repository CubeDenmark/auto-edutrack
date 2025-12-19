<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
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

        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    }
}
