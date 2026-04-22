<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateVendorProfileRequest;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    /**
     * Show the vendor profile edit form
     */
    public function edit()
    {
        $vendor = Auth::user()->vendor;
        $user = Auth::user();
        
        return view('vendor.profile.edit', compact('vendor', 'user'));
    }

    /**
     * Update vendor profile information
     */
    public function update(UpdateVendorProfileRequest $request)
    {
        $user = Auth::user();
        $vendor = $user->vendor;

        $validated = $request->validated();

        // Update user info
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        // Update vendor info
        $vendor->update([
            'business_name' => $validated['business_name'],
            'business_phone' => $validated['business_phone'],
            'business_address' => $validated['business_address'],
            'latitude' => $validated['latitude'] ?? $vendor->latitude,
            'longitude' => $validated['longitude'] ?? $vendor->longitude,
            'operating_hours' => $validated['operating_hours'] ?? $vendor->operating_hours,
        ]);

        return redirect()
            ->route('vendor.profile.edit')
            ->with('success', 'Profile updated successfully!');
    }

        /**
     * Update profile picture
     */
    public function updatePicture(Request $request)
    {
        $request->validate([
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'picture.required' => 'Please select a picture to upload.',
            'picture.image' => 'The file must be an image.',
            'picture.mimes' => 'The image must be JPEG, PNG, JPG, or GIF.',
            'picture.max' => 'The image cannot exceed 2MB.',
        ]);

        try {
            $user = Auth::user();

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
                    'path' => $path
                ]);
            }

            return redirect()
                ->route('vendor.profile.edit')
                ->with('success', 'Profile picture updated successfully!');
                
        } catch (\Exception $e) {
            \Log::error('Profile picture upload failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return redirect()
                ->route('vendor.profile.edit')
                ->with('error', 'Failed to upload profile picture. Please try again.');
        }
    }


    /**
     * Get vendor analytics and stats
     */
    public function getStats()
    {
        $vendor = Auth::user()->vendor;

        return response()->json([
            'total_products' => $vendor->products()->count(),
            'total_orders' => $vendor->orders()->count(),
            'completed_orders' => $vendor->orders()->where('status', 'delivered')->count(),
            'pending_orders' => $vendor->orders()->whereIn('status', ['pending', 'confirmed', 'preparing'])->count(),
            'total_revenue' => $vendor->orders()->where('status', 'delivered')->sum('subtotal'),
            'avg_rating' => $vendor->rating,
        ]);
    }
}
