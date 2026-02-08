<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    /**
     * Show the two factor challenge view.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        if (!$request->session()->has('auth.2fa.user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/TwoFactorChallenge');
    }

    /**
     * Handle the two factor authentication challenge.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('auth.2fa.user_id');
        $remember = $request->session()->get('auth.2fa.remember', false);

        if (!$userId) {
            return redirect()->route('login');
        }

        /** @var \App\Models\User $user */
        $user = User::findOrFail($userId);

        if ($user->verifyOtp($request->code)) {
            Auth::login($user, $remember);
            
            $request->session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        throw ValidationException::withMessages([
            'code' => __('The provided two-factor authentication code was invalid.'),
        ]);
    }

    /**
     * Redirect the user based on their specific role.
     */
    protected function redirectBasedOnRole($user): RedirectResponse
    {
        if ($user->hasRole('SuperAdmin') || $user->hasRole('Admin')) {
            return redirect()->intended(route('dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }
}
