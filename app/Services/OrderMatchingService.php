<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Vendor;
use App\Models\Rider;

class OrderMatchingService
{
    /**
     * Match order with nearest available vendor
     */
    public function matchVendor(Order $order): ?Vendor
    {
        $maxDistance = config('app.max_delivery_distance', 15); // km
        
        $vendors = Vendor::query()
            ->where('is_open', true)
            ->where('is_verified', true)
            ->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( latitude ) ) ) ) AS distance", 
                [$order->latitude ?? 0, $order->longitude ?? 0, $order->latitude ?? 0])
            ->having('distance', '<', $maxDistance)
            ->orderBy('distance')
            ->first();

        return $vendors;
    }

    /**
     * Match order with nearest available rider
     */
    public function matchRider(Order $order): ?Rider
    {
        $maxDistance = config('app.max_delivery_distance', 15); // km
        
        $riders = Rider::query()
            ->where('is_available', true)
            ->where('is_verified', true)
            ->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( current_latitude ) ) * cos( radians( current_longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( current_latitude ) ) ) ) AS distance", 
                [$order->latitude ?? 0, $order->longitude ?? 0, $order->latitude ?? 0])
            ->having('distance', '<', $maxDistance)
            ->orderBy('distance')
            ->first();

        return $riders;
    }

    /**
     * Find nearest available rider to a vendor
     */
    public function findNearestRider(Vendor $vendor): ?Rider
    {
        $maxDistance = config('app.max_delivery_distance', 15); // km
        
        $rider = Rider::query()
            ->where('is_available', true)
            ->where('is_verified', true)
            ->selectRaw("*, ( 6371 * acos( cos( radians(?) ) * cos( radians( current_latitude ) ) * cos( radians( current_longitude ) - radians(?) ) + sin( radians(?) ) * sin( radians( current_latitude ) ) ) ) AS distance", 
                [$vendor->latitude ?? 0, $vendor->longitude ?? 0, $vendor->latitude ?? 0])
            ->having('distance', '<', $maxDistance)
            ->orderBy('distance')
            ->first();

        return $rider;
    }

    /**
     * Auto-match order with vendor and rider
     */
    public function autoMatch(Order $order): bool
    {
        $vendor = $this->matchVendor($order);
        if (!$vendor) {
            return false;
        }

        $rider = $this->matchRider($order);
        if (!$rider) {
            return false;
        }

        // Update order with matched vendor and rider
        $order->update([
            'vendor_id' => $vendor->id,
            'rider_id' => $rider->id,
            'status' => 'confirmed',
        ]);

        // Create tracking record
        $order->tracking()->create([
            'status' => 'confirmed',
            'notes' => 'Order confirmed and assigned to vendor and rider',
        ]);

        return true;
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earth_radius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));
        $distance = $earth_radius * $c;

        return round($distance, 2);
    }
}
