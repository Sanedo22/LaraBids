<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class SocialController extends Controller
{
    // Redirect user to Google
    public function redirect(Request $request, $provider)
    {
        $driver = Socialite::driver($provider);

        // If user selected a specific saved account
        if ($request->has('hint')) {
            $driver->with(['login_hint' => $request->hint]);
        } else {
            // Force account selection if no hint
            $driver->with(['prompt' => 'select_account']);
        }

        return $driver->redirect();
    }

    // Handle Google callback after authentication
    public function callback(Request $request, $provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            $isNewUser = false;

            // First try to find user by google_id
            $user = User::where('google_id', $socialUser->getId())->first();

            if (!$user) {
                // Check if user already exists with same email
                $user = User::where('email', $socialUser->getEmail())->first();

                if ($user) {
                    // Link google_id to existing account and verify email
                    $updateData = ['google_id' => $socialUser->getId()];
                    if (!$user->email_verified_at) {
                        $updateData['email_verified_at'] = now();
                    }
                    $user->update($updateData);
                } else {
                    $isNewUser = true;
                    // Create a brand new user
                    // Generate a unique username from name
                    $baseUsername = Str::slug($socialUser->getName() ?: 'user', '');
                    $username = $baseUsername;
                    $counter = 1;
                    
                    while (User::where('username', $username)->exists()) {
                        $username = $baseUsername . $counter;
                        $counter++;
                    }

                    $user = User::create([
                        'name'              => $socialUser->getName(),
                        'username'          => $username,
                        'email'             => $socialUser->getEmail(),
                        'google_id'         => $socialUser->getId(),
                        'email_verified_at' => now(),
                        'password'          => null,
                    ]);
                    // Assign default role via Spatie
                    $user->assignRole('user');
                }
            }

            // Save last used Google account gracefully for 1 year (minutes)
            Cookie::queue('last_oauth_email', $socialUser->getEmail(), 525600);
            Cookie::queue('last_oauth_name', $user->name, 525600);
            Cookie::queue('last_oauth_avatar', $socialUser->getAvatar(), 525600);

            // Login user WITHOUT remember me (respects session lifetime)
            Auth::login($user, false);

            // Standard Laravel login session regeneration
            $request->session()->regenerate();

            $authUser = Auth::user();

            if ($isNewUser) {
                return redirect()->route('user.kyc.form');
            }

            if (!$authUser->isKycApproved()) {
                return redirect()->route('user.kyc.form');
            }

            if ($authUser->isAdmin() || $authUser->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }

            return redirect()->route('home');


        } catch (\Exception $e) {
            Log::error('Google login failed: ' . $e->getMessage());
            return redirect()->route('login')->with('error', 'Google login failed. Please try again.');
        }
    }
}
