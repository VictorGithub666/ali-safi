<?php

namespace App\Services;

class DistanceService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     *
     * @param float $lat1 Latitude of point 1
     * @param float $lon1 Longitude of point 1
     * @param float $lat2 Latitude of point 2
     * @param float $lon2 Longitude of point 2
     * @return float Distance in kilometers
     */
    public static function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {

        $lat1 = (float) $lat1;
        $lon1 = (float) $lon1;
        $lat2 = (float) $lat2;
        $lon2 = (float) $lon2;

        // Earth's radius in kilometers
        $earthRadius = 6371;

        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        // Differences
        $dLat = $lat2Rad - $lat1Rad;
        $dLon = $lon2Rad - $lon1Rad;

        // Haversine formula
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earthRadius * $c;

        return round($distance, 2);
    }

    /**
     * Estimate distance in kilometers with formatted output
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return string Formatted distance string
     */
    public static function formatDistance($lat1, $lon1, $lat2, $lon2)
    {
        $distance = self::calculateDistance($lat1, $lon1, $lat2, $lon2);
        
        if ($distance < 1) {
            return number_format($distance * 1000, 0) . 'm';
        }
        
        return number_format($distance, 1) . ' km';
    }

    /**
     * Estimate delivery time based on distance
     * Assumes average speed of 30 km/h in urban areas
     *
     * @param float $distance Distance in kilometers
     * @return string Estimated time
     */
    public static function estimateDeliveryTime($distance)
    {
        // Average urban speed: 30 km/h
        $speedKmh = 30;
        $timeHours = $distance / $speedKmh;
        $timeMinutes = $timeHours * 60;

        if ($timeMinutes < 1) {
            return '< 1 min';
        } elseif ($timeMinutes < 60) {
            return round($timeMinutes) . ' min';
        } else {
            $hours = floor($timeMinutes / 60);
            $mins = $timeMinutes % 60;
            return $hours . 'h ' . round($mins) . 'min';
        }
    }
}
