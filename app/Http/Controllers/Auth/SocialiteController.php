<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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
            $user = Socialite::driver('google')->user();

            // Check if user exists
            $existingUser = User::where('google_id', $user->getId())->first();

            if ($existingUser) {
                Auth::login($existingUser, true);

                return redirect()->route($this->redirectBasedOnRole($existingUser));
            } else {
                // Check if email exists
                $emailUser = User::where('email', $user->getEmail())->first();

                if ($emailUser) {
                    // Update google_id
                    $emailUser->update(['google_id' => $user->getId()]);
                    Auth::login($emailUser, true);

                    return redirect()->route($this->redirectBasedOnRole($emailUser));
                }

                // Create new user - default to customer
                $newUser = User::create([
                    'name' => $user->getName(),
                    'email' => $user->getEmail(),
                    'google_id' => $user->getId(),
                    'password' => bcrypt('password'), // Random password for OAuth users
                    'user_type' => 'customer',
                    'is_verified' => true, // Google verifies emails
                    'email_verified_at' => now(),
                ]);

                Auth::login($newUser, true);

                return redirect()->route('customer.dashboard');
            }
        } catch (Exception $e) {
            return redirect('login')->with('error', 'Failed to login with Google');
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
