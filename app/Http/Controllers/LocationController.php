<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function getCounties()
    {
        $locations = require app_path('Data/KenyaLocations.php');
        return response()->json(array_keys($locations));
    }

    public function getSubCounties($county)
    {
        $locations = require app_path('Data/KenyaLocations.php');
        
        if (!isset($locations[$county])) {
            return response()->json(['error' => 'County not found'], 404);
        }

        return response()->json(array_keys($locations[$county]));
    }

    public function getWards($county, $subCounty)
    {
        $locations = require app_path('Data/KenyaLocations.php');
        
        if (!isset($locations[$county][$subCounty])) {
            return response()->json(['error' => 'Sub-county not found'], 404);
        }

        return response()->json($locations[$county][$subCounty]);
    }
}
