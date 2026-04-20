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

    public function updatePicture(Request $request)
    {
        $request->validate([
            'profile_picture' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $user = auth()->user();

        // Delete old picture if exists
        if ($user->profile_picture && Storage::exists('public/' . $user->profile_picture)) {
            Storage::delete('public/' . $user->profile_picture);
        }

        // Store new picture
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $filename = 'profile-' . auth()->id() . '-' . time() . '.' . $file->getClientOriginalExtension();

            // Optimize and save image
            $image = Image::read($file);
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            $path = 'public/profile-pictures/' . $filename;
            Storage::put($path, $image->tostring()->toString());

            $user->update([
                'profile_picture' => 'profile-pictures/' . $filename,
            ]);
        }

        return redirect()->route('profile.edit')->with('success', 'Profile picture updated successfully');
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
