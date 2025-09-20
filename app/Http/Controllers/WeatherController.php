<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Snapshot;
use App\Models\WeatherReport;
use Cache;
use Http;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WeatherController extends Controller
{
    public function point(Request $req)
    {
        $lat = $req->query('lat');
        $lon = $req->query('lon');

        if (! $lat || ! $lon) return response()->json(['error'=>'lat/lon required'], 422);

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

    // ✅ NEW: Store real-time snapshot from current weather data
    public function storeCurrentWeatherSnapshot(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'location_name' => 'nullable|string',
            'temperature' => 'required|numeric',
            'feels_like' => 'required|numeric',
            'humidity' => 'required|numeric',
            'pressure' => 'required|numeric',
            'wind_speed' => 'nullable|numeric',
            'wind_direction' => 'nullable|string',
            'cloudiness' => 'nullable|numeric',
            'precipitation' => 'nullable|numeric',
            'weather_main' => 'nullable|string',
            'weather_desc' => 'nullable|string',
            'weather_icon' => 'nullable|string',
        ]);

        try {
            // Find or create location
            $location = $this->findOrCreateLocation(
                $request->latitude, 
                $request->longitude, 
                $request->location_name
            );

            // Get or create today's weather report
            $weatherReport = $this->getOrCreateTodaysReport($location->locID);

            // Determine current snapshot time
            $snapshotTime = $this->determineSnapshotTime();

            // Create the snapshot with real-time data
            $snapshot = Snapshot::updateOrCreate(
                [
                    'wrID' => $weatherReport->wrID,
                    'snapshot_time' => $snapshotTime,
                ],
                [
                    'temperature' => $request->temperature,
                    'feels_like' => $request->feels_like,
                    'humidity' => $request->humidity,
                    'pressure' => $request->pressure,
                    'wind_speed' => $request->wind_speed ?? 0,
                    'wind_direction' => $request->wind_direction ?? '0',
                    'cloudiness' => $request->cloudiness ?? 0,
                    'precipitation' => $request->precipitation ?? 0,
                    'weather_main' => $request->weather_main ?? '',
                    'weather_desc' => $request->weather_desc ?? '',
                    'weather_icon' => $request->weather_icon ?? '',
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Real-time weather snapshot saved successfully',
                'data' => [
                    'location' => $location,
                    'snapshot' => $snapshot,
                    'snapshot_time' => $snapshotTime
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save weather snapshot: ' . $e->getMessage()
            ], 500);
        }
    }

    // ✅ Store daily forecast (existing functionality)
    public function storeDailyForecast(Request $request)
    {
        $request->validate([
            'locID' => 'required|integer|exists:locations,locID',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        return $this->processDailyForecast(
            $request->locID,
            $request->latitude,
            $request->longitude
        );
    }

    // ✅ Fetch and store daily forecast based on location
    public function fetchAndStoreDailyForecast($locID)
    {
        $location = Location::findOrFail($locID);

        $this->processDailyForecast(
            $location->locID,
            $location->latitude,
            $location->longitude
        );

        return redirect()->back()->with('success', 'Daily forecast stored!');
    }

    // ✅ Process daily forecast using OneCall API
    private function processDailyForecast($locID, $lat, $lon)
    {
        $apiKey = env('OPENWEATHER_API_KEY');
        $url = "https://api.openweathermap.org/data/3.0/onecall?lat={$lat}&lon={$lon}&exclude=minutely,hourly,alerts&units=metric&appid={$apiKey}";
        $response = Http::get($url);

        if ($response->failed()) {
            throw new \Exception("Failed to fetch daily forecast data");
        }

        $data = $response->json();

        $today = now()->toDateString();
        $weatherReport = WeatherReport::firstOrCreate([
            'locID' => $locID,
            'report_date' => $today,
        ]);

        $daily = $data['daily'][0];

        $snapshots = [
            'morning' => $daily['temp']['morn'],
            'noon' => $daily['temp']['day'],
            'afternoon' => $daily['temp']['eve'],
            'evening' => $daily['temp']['night'],
        ];

        foreach ($snapshots as $time => $temp) {
            Snapshot::updateOrCreate(
                [
                    'wrID' => $weatherReport->wrID,
                    'snapshot_time' => $time,
                ],
                [
                    'temperature' => $temp,
                    'feels_like' => $daily['feels_like'][$this->mapSnapshotKey($time)],
                    'humidity' => $daily['humidity'],
                    'pressure' => $daily['pressure'],
                    'wind_speed' => $daily['wind_speed'],
                    'wind_direction' => $daily['wind_deg'],
                    'cloudiness' => $daily['clouds'],
                    'precipitation' => $daily['pop'] ?? 0,
                    'weather_main' => $daily['weather'][0]['main'] ?? '',
                    'weather_desc' => $daily['weather'][0]['description'] ?? '',
                    'weather_icon' => $daily['weather'][0]['icon'] ?? '',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Daily forecast stored successfully'
        ]);
    }

    // ✅ Map snapshot time to forecast key
    private function mapSnapshotKey($time)
    {
        $mapping = [
            'morning' => 'morn',
            'noon' => 'day', 
            'afternoon' => 'eve',
            'evening' => 'night'
        ];
        
        return $mapping[$time] ?? 'day';
    }

    private function findOrCreateLocation($latitude, $longitude, $locationName = null)
    {
        $existingLocation = Location::where('latitude', '>=', $latitude - 0.01)
            ->where('latitude', '<=', $latitude + 0.01)
            ->where('longitude', '>=', $longitude - 0.01)
            ->where('longitude', '<=', $longitude + 0.01)
            ->first();

        if ($existingLocation) {
            return $existingLocation;
        }

        return Location::create([
            'name' => $locationName ?: "Location ({$latitude}, {$longitude})",
            'latitude' => $latitude,
            'longitude' => $longitude
        ]);
    }

    private function getOrCreateTodaysReport($locID)
    {
        $today = now()->toDateString();

        return WeatherReport::firstOrCreate(
            [
                'locID' => $locID,
                'report_date' => $today
            ]
        );
    }

    private function determineSnapshotTime()
    {
        $hour = now()->hour;

        if ($hour >= 5 && $hour < 11) {
            return 'morning';
        } elseif ($hour >= 11 && $hour < 14) {
            return 'noon';
        } elseif ($hour >= 14 && $hour < 18) {
            return 'afternoon';
        } else {
            return 'evening';
        }
    }

    private function calculateStormStatus($precipitation)
    {
        if ($precipitation > 10) {
            return 'severe';
        } elseif ($precipitation > 3) {
            return 'moderate';
        } elseif ($precipitation > 0) {
            return 'light';
        }
        
        return 'none';
    }

    public function getLocationWeatherHistory($locID, $days = 7)
    {
        try {
            $location = Location::findOrFail($locID);
            
            $reports = WeatherReport::with('snapshots')
                ->where('locID', $locID)
                ->where('report_date', '>=', now()->subDays($days))
                ->orderBy('report_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'location' => $location,
                'reports' => $reports
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch weather history: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTodaysWeatherSnapshots()
    {
        try {
            $today = now()->toDateString();
            
            $snapshots = Snapshot::with(['weatherReport.location'])
                ->whereHas('weatherReport', function($query) use ($today) {
                    $query->where('report_date', $today);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'snapshots' => $snapshots->map(function($snapshot) {
                    return [
                        'snapshot_id' => $snapshot->snapshotID,
                        'location' => $snapshot->weatherReport->location->name,
                        'latitude' => $snapshot->weatherReport->location->latitude,
                        'longitude' => $snapshot->weatherReport->location->longitude,
                        'snapshot_time' => $snapshot->snapshot_time,
                        'temperature' => $snapshot->temperature,
                        'feels_like' => $snapshot->feels_like,
                        'humidity' => $snapshot->humidity,
                        'precipitation' => $snapshot->precipitation,
                        'storm_status' => $snapshot->storm_status,
                        'weather_desc' => $snapshot->weather_desc,
                        'weather_icon' => $snapshot->weather_icon,
                        'created_at' => $snapshot->created_at
                    ];
                })
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch snapshots: ' . $e->getMessage()
            ], 500);
        }
    }
}