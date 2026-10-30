<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $isDemoMode = (bool) config('app.demo_mode', false) && config('app.env') !== 'production';

        return view('auth.login', [
            'isDemoMode' => $isDemoMode,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'login.' . $request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function demoLogin(Request $request): RedirectResponse
    {
        // Enforce demo mode guard
        if (!config('app.demo_mode') || config('app.env') === 'production') {
            abort(403, 'One-click demo login is disabled.');
        }

        $request->validate([
            'role' => ['required', 'in:owner,viewer'],
        ]);

        $role = $request->input('role');
        $email = ($role === 'owner') ? 'owner@example.com' : 'viewer@example.com';

        $user = User::where('email', $email)->first();
        if (!$user) {
            // Seed on the fly if needed
            $user = User::create([
                'name' => ($role === 'owner') ? 'Demo Owner' : 'Demo Reviewer',
                'email' => $email,
                'password' => bcrypt('password'),
                'role' => $role,
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', "Signed in as {$user->name} ({$role}).");
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('info', 'You have been signed out.');
    }
}
