<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialiteController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Check if user exists by google_id
            $existingUser = User::where('google_id', $googleUser->getId())->first();

            if ($existingUser) {
                // Login existing user
                Auth::login($existingUser, true);
                return redirect()->route($this->redirectBasedOnRole($existingUser));
            }

            // Check if email exists
            $emailUser = User::where('email', $googleUser->getEmail())->first();

            if ($emailUser) {
                // Update existing user with google_id
                $emailUser->update(['google_id' => $googleUser->getId()]);
                Auth::login($emailUser, true);
                return redirect()->route($this->redirectBasedOnRole($emailUser));
            }

            // Create new user - Force customer type only
            $newUser = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(24)), // Random secure password
                'user_type' => 'customer', // Force customer type
                'is_active' => true,
                'is_verified' => true, // Google verifies emails
                'email_verified_at' => now(),
                'phone' => null, // Will be updated later in profile
            ]);

            Auth::login($newUser, true);

            // Redirect to customer dashboard
            return redirect()->route('customer.dashboard')
                ->with('success', 'Welcome to Ali-Safi! Please complete your profile.');

        } catch (Exception $e) {
            \Log::error('Google OAuth Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')
                ->with('error', 'Failed to login with Google. Please try again.');
        }
    }

    protected function redirectBasedOnRole($user)
    {
        switch ($user->user_type) {
            case 'admin':
                return 'admin.dashboard';
            case 'vendor':
                return 'vendor.dashboard';
            case 'rider':
                return 'rider.dashboard';
            default:
                return 'customer.dashboard';
        }
    }
}