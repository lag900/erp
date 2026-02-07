<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('Login attempt for: ' . $request->email);
        $user = \App\Models\User::where('email', $request->email)->first();
        if (!$user) {
            \Illuminate\Support\Facades\Log::warning('User not found: ' . $request->email);
        } else {
            \Illuminate\Support\Facades\Log::info('User found in DB. ID: ' . $user->id);
            if (\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
                \Illuminate\Support\Facades\Log::info('Password check passed in controller.');
            } else {
                \Illuminate\Support\Facades\Log::warning('Password check failed in controller.');
            }
        }
        
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
