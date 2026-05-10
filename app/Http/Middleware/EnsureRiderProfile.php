<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Rider;

class EnsureRiderProfile
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        
        if (!$user->rider) {
            // Create rider profile automatically
            $rider = Rider::create([
                'user_id' => $user->id,
                'vehicle_type' => 'motorcycle',
                'vehicle_number' => 'PENDING',
                'license_number' => 'PENDING',
                'is_available' => false,
                'is_verified' => false,
                'total_deliveries' => 0,
                'wallet_balance' => 0,
            ]);
            
            // Refresh the relationship
            $user->refresh();
        }
        
        if (!$user->rider) {
            return redirect()->route('profile.edit')
                ->with('error', 'Unable to setup rider profile. Please contact support.');
        }
        
        return $next($request);
    }
}