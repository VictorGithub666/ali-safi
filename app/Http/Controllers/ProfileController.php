<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
        ]);

        auth()->user()->update($validated);

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully');
    }

    /**
     * Update profile picture - works for ALL user types
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ], [
            'picture.required' => 'Please select a picture to upload.',
            'picture.image' => 'The file must be an image.',
            'picture.mimes' => 'The image must be JPEG, PNG, JPG, or GIF.',
            'picture.max' => 'The image cannot exceed 2MB.',
        ]);

        try {
            $user = auth()->user();

            // Delete old picture if exists
            if ($user->profile_picture) {
                Storage::disk('public')->delete($user->profile_picture);
            }

            // Upload new picture
            if ($request->hasFile('picture')) {
                $path = $request->file('picture')->store('profile-pictures', 'public');
                $user->update(['profile_picture' => $path]);
                
                \Log::info('Profile picture updated', [
                    'user_id' => $user->id,
                    'user_type' => $user->user_type,
                    'path' => $path
                ]);
            }

            return redirect()
                ->route('profile.edit')
                ->with('success', 'Profile picture updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Profile picture upload failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()
                ->route('profile.edit')
                ->with('error', 'Failed to upload profile picture. Please try again.');
        }
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('profile.edit')->with('success', 'Password updated successfully');
    }
}
