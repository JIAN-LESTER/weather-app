<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Snapshot;
use App\Models\Weather_Report;
use Cache;
use Dotenv\Exception\ValidationException;
use Http;
use Illuminate\Http\Request;

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

     public function storeWeatherSnapshot(Request $request)
    {
        try {
            $validated = $request->validate([
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'location_name' => 'nullable|string|max:255',
                'temperature' => 'required|numeric',
                'feels_like' => 'required|numeric',
                'humidity' => 'required|integer|between:0,100',
                'pressure' => 'required|integer',
                'wind_speed' => 'required|numeric|min:0',
                'wind_direction' => 'required|string|max:5',
                'cloudiness' => 'required|integer|between:0,100',
                'precipitation' => 'required|numeric|min:0',
                'weather_main' => 'required|string',
                'weather_desc' => 'required|string',
                'weather_icon' => 'required|string',
            ]);

     
            $location = $this->findOrCreateLocation(
                $validated['latitude'],
                $validated['longitude'],
                $validated['location_name'] ?? null
            );

   
            $weatherReport = $this->getOrCreateTodaysReport($location->locID);

          
            $snapshotTime = $this->determineSnapshotTime();

  
            $stormStatus = $this->calculateStormStatus($validated['precipitation']);

            $snapshot = Snapshot::updateOrCreate(
                [
                    'wrID' => $weatherReport->wrID,
                    'snapshot_time' => $snapshotTime
                ],
                [
                    'temperature' => $validated['temperature'],
                    'feels_like' => $validated['feels_like'],
                    'humidity' => $validated['humidity'],
                    'pressure' => $validated['pressure'],
                    'wind_speed' => $validated['wind_speed'],
                    'wind_direction' => $validated['wind_direction'],
                    'cloudiness' => $validated['cloudiness'],
                    'precipitation' => $validated['precipitation'],
                    'weather_main' => $validated['weather_main'],
                    'weather_desc' => $validated['weather_desc'],
                    'weather_icon' => $validated['weather_icon'],
                    'storm_status' => $stormStatus,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Weather data saved successfully',
                'data' => [
                    'snapshot_id' => $snapshot->snapshotID,
                    'location' => $location->name,
                    'snapshot_time' => $snapshotTime,
                    'storm_status' => $stormStatus
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save weather data: ' . $e->getMessage()
            ], 500);
        }
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

        return Weather_Report::firstOrCreate(
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
            
            $reports = Weather_Report::with('snapshots')
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
