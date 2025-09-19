<?php

namespace App\Http\Controllers;

use Cache;
use Http;
use Illuminate\Http\Request;

class WeatherController extends Controller
{
    public function point(Request $req)
    {
        $lat = $req->query('lat');
        $lon = $req->query('lon');

        if (! $lat || ! $lon) return response()->json(['error'=>'lat/lon required'], 422);

        // cache key unique per coordinate pair (round coords to reduce unique keys)
        $cacheKey = 'weather:'.round($lat,4).':'.round($lon,4);

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($lat, $lon) {
            $apiKey = config('services.openweather.key');
            $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
            $resp = Http::timeout(10)->get($url);

            if ($resp->failed()) {
                return ['error' => 'failed_fetch'];
            }
            return $resp->json();
        });

        return response()->json($data);
    }
}
