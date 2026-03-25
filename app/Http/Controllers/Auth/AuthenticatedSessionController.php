<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // Redirect to KYC if not submitted/approved, except for admins
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            if (!$user->kyc || $user->kyc->status === 'rejected') {
                return redirect()->route('user.kyc.form')->with('warning', 'Please complete your KYC verification to continue.');
            }
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->route('home');
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
