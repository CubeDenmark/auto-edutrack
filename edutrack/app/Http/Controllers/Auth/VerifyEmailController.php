<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Skip email verification entirely for student and parent
        if (in_array($user->user_type, ['student', 'parent'])) {
            return $this->redirectWithVerifiedFlag($user->user_type);
        }

        // Continue with verification for prof
        if ($user->hasVerifiedEmail()) {
            return $this->redirectWithVerifiedFlag($user->user_type);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return $this->redirectWithVerifiedFlag($user->user_type);
    }

    private function redirectWithVerifiedFlag(?string $userType): RedirectResponse
    {
        $route = match ($userType) {
            'prof' => 'prof.dashboard',
            'parent' => 'parent.dashboard',
            'student' => 'student.dashboard',
            default => '/',
        };

        return redirect()->intended(route($route, absolute: false) . '?verified=1');
    }
}
