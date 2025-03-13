<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GoogleMapsController extends Controller
{
    public function getApiKey()
    {
        $apiKey = config('services.google.maps.api_key');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key no encontrada'], 500);
        }

        return response()->json(['apiKey' => $apiKey]);
    }
}
