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

        if (!$lat || !$lon)
            return response()->json(['error' => 'lat/lon required'], 422);

        $cacheKey = 'weather:' . round($lat, 4) . ':' . round($lon, 4);

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

    public function getCurrentWeatherData(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->lat;
        $lon = $request->lon;
        $cacheKey = 'current-weather:' . round($lat, 4) . ':' . round($lon, 4);

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($lat, $lon) {
                $apiKey = config('services.openweather.key');

                if (!$apiKey) {
                    throw new \Exception('OpenWeatherMap API key not configured');
                }

                $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";
                $response = Http::timeout(15)->get($url);

                if ($response->failed()) {
                    throw new \Exception('Failed to fetch current weather: HTTP ' . $response->status());
                }

                return $response->json();
            });

            return response()->json($data);

        } catch (\Exception $e) {
            \Log::error('Current weather fetch error: ' . $e->getMessage(), [
                'lat' => $lat,
                'lon' => $lon
            ]);

            return response()->json([
                'error' => 'Failed to fetch current weather data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get full day forecast data
     */


    /**
     * Get full day forecast data for all time periods
     */
    public function storeFullDayForecastSnapshots(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            'time_slots' => 'required|array',
            'time_slots.morning' => 'nullable|array',
            'time_slots.noon' => 'nullable|array',
            'time_slots.afternoon' => 'nullable|array',
            'time_slots.evening' => 'nullable|array',
            'time_slots.*.temperature' => 'nullable|numeric|between:-50,60',
            'time_slots.*.feels_like' => 'nullable|numeric|between:-50,60',
            'time_slots.*.precipitation' => 'nullable|numeric|min:0',
            'time_slots.*.weather_main' => 'nullable|string|max:50',
            'time_slots.*.weather_desc' => 'nullable|string|max:255',
            'time_slots.*.weather_icon' => 'nullable|string|max:10',
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

            $processedSnapshots = [];
            $timePeriods = ['morning', 'noon', 'afternoon', 'evening'];

            foreach ($timePeriods as $timeSlot) {
                $data = $request->time_slots[$timeSlot] ?? null;

                if ($data && isset($data['temperature'])) {
                    $precipitation_mm = $data['precipitation_mm'] ?? 0;
                    $precipitation_chance = $data['precipitation_chance'] ?? 0;

                    $processedSnapshots[$timeSlot] = [
                        'temperature' => round($data['temperature'], 1),
                        'feels_like' => round($data['feels_like'] ?? $data['temperature'], 1),
                        'precipitation_mm' => round($precipitation_mm, 2),
                        'precipitation_chance' => $precipitation_chance,
                        'storm_status' => $this->calculateStormStatus($precipitation_mm),
                        'weather_main' => $data['weather_main'] ?? '',
                        'weather_desc' => $data['weather_desc'] ?? '',
                        'weather_icon' => $data['weather_icon'] ?? '',
                        'humidity' => $data['humidity'] ?? 0,
                        'pressure' => $data['pressure'] ?? 0,
                        'wind_speed' => round($data['wind_speed'] ?? 0, 1),
                        'wind_direction' => $data['wind_direction'] ?? '0',
                        'cloudiness' => $data['cloudiness'] ?? 0,
                        'forecast_time' => $data['forecast_time'] ?? null,
                        'recorded_at' => now()->toISOString(),
                    ];

                }
            }

            if (empty($processedSnapshots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid weather data provided for any time period'
                ], 400);
            }

            // Store all snapshots in the JSON column
            $snapshotRow = Snapshot::updateOrCreate(
                ['wrID' => $weatherReport->wrID],
                ['snapshots' => $processedSnapshots]
            );

            return response()->json([
                'success' => true,
                'message' => 'Full day weather snapshots saved successfully',
                'data' => [
                    'location' => [
                        'id' => $location->locID,
                        'name' => $location->name,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                    'weather_report_id' => $weatherReport->wrID,
                    'snapshot_id' => $snapshotRow->snapshotID,
                    'time_periods_saved' => array_keys($processedSnapshots),
                    'total_periods' => count($processedSnapshots)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Full day snapshot storage error: ' . $e->getMessage(), [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save full day snapshots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's weather snapshots with proper JSON handling
     */
    public function getTodaysWeatherSnapshots()
    {
        try {
            $today = now()->toDateString();

            $snapshots = Snapshot::with(['weatherReport.location'])
                ->whereHas('weatherReport', function ($query) use ($today) {
                    $query->where('report_date', $today);
                })
                ->whereNotNull('snapshots')
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedSnapshots = [];

            foreach ($snapshots as $snapshot) {
                $location = $snapshot->weatherReport->location;
                $snapshotData = $snapshot->snapshots;

                if (!$snapshotData || !is_array($snapshotData)) {
                    continue;
                }

                foreach ($snapshotData as $timeSlot => $data) {
                    $formattedSnapshots[] = [
                        'snapshot_id' => $snapshot->snapshotID,
                        'location' => $location->name,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'snapshot_time' => $timeSlot,
                        'temperature' => $data['temperature'] ?? 0,
                        'feels_like' => $data['feels_like'] ?? 0,
                        'humidity' => $data['humidity'] ?? 0,
                        'precipitation' => $data['precipitation'] ?? 0,
                        'storm_status' => $data['storm_status'] ?? 'none',
                        'weather_desc' => $data['weather_desc'] ?? '',
                        'weather_icon' => $data['weather_icon'] ?? '',
                        'created_at' => $snapshot->created_at,
                        'recorded_at' => $data['recorded_at'] ?? $snapshot->created_at,
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'snapshots' => $formattedSnapshots
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching today\'s snapshots: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch snapshots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get full day forecast data for all time periods
     */
    public function getFullDayForecastData(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lon' => 'required|numeric|between:-180,180'
        ]);

        $lat = $request->lat;
        $lon = $request->lon;

        $cacheKey = 'full-forecast:' . round($lat, 4) . ':' . round($lon, 4);

        try {
            $data = Cache::remember($cacheKey, now()->addMinutes(15), function () use ($lat, $lon) {
                $apiKey = config('services.openweather.key');

                if (!$apiKey) {
                    throw new \Exception('OpenWeatherMap API key not configured');
                }

                $url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&units=metric&appid={$apiKey}";

                $response = Http::timeout(15)->get($url);

                if ($response->failed()) {
                    throw new \Exception('Failed to fetch forecast data: HTTP ' . $response->status());
                }

                return $response->json();
            });

            // Process the forecast data to extract all time periods
            $processedData = $this->processFullDayForecastData($data);

            return response()->json($processedData);

        } catch (\Exception $e) {
            \Log::error('Forecast fetch error: ' . $e->getMessage(), [
                'lat' => $lat,
                'lon' => $lon
            ]);

            return response()->json([
                'error' => 'Failed to fetch forecast data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process forecast data to extract weather for all time periods
     */
    private function processFullDayForecastData($forecastData)
    {
        if (!isset($forecastData['list']) || empty($forecastData['list'])) {
            throw new \Exception('Invalid forecast data structure');
        }

        $timeSlots = [
            'morning' => null,
            'noon' => null,
            'afternoon' => null,
            'evening' => null
        ];

        foreach ($forecastData['list'] as $forecast) {
            $dateTime = new \DateTime($forecast['dt_txt']);
            $hour = (int) $dateTime->format('H');
            $timeSlot = null;

            if ($hour >= 6 && $hour <= 10 && !$timeSlots['morning'])
                $timeSlot = 'morning';
            elseif ($hour >= 11 && $hour <= 14 && !$timeSlots['noon'])
                $timeSlot = 'noon';
            elseif ($hour >= 15 && $hour <= 17 && !$timeSlots['afternoon'])
                $timeSlot = 'afternoon';
            elseif ($hour >= 18 && $hour <= 22 && !$timeSlots['evening'])
                $timeSlot = 'evening';

            if ($timeSlot && !$timeSlots[$timeSlot]) {
                $rain = $forecast['rain']['3h'] ?? 0;
                $snow = $forecast['snow']['3h'] ?? 0;
                $precipitation_mm = $rain + $snow;

                // OpenWeatherMap pop is 0–1
                $precipitation_chance = isset($forecast['pop']) ? round($forecast['pop'] * 100) : ($precipitation_mm > 0 ? 100 : 0);

                $timeSlots[$timeSlot] = [
                    'temperature' => round($forecast['main']['temp'], 1),
                    'feels_like' => round($forecast['main']['feels_like'], 1),
                    'precipitation_mm' => round($precipitation_mm, 2),
                    'precipitation_chance' => $precipitation_chance,
                    'storm_status' => $this->calculateStormStatus($precipitation_mm),
                    'weather_main' => $forecast['weather'][0]['main'] ?? '',
                    'weather_desc' => $forecast['weather'][0]['description'] ?? '',
                    'weather_icon' => $forecast['weather'][0]['icon'] ?? '',
                    'humidity' => $forecast['main']['humidity'] ?? 0,
                    'pressure' => $forecast['main']['pressure'] ?? 0,
                    'wind_speed' => round($forecast['wind']['speed'] ?? 0, 1),
                    'wind_direction' => $forecast['wind']['deg'] ?? 0,
                    'cloudiness' => $forecast['clouds']['all'] ?? 0,
                    'forecast_time' => $forecast['dt_txt'],
                    'hour' => $hour
                ];
            }
        }

        return [
            'city' => $forecastData['city'] ?? [],
            'time_slots' => $timeSlots,
            'location' => [
                'name' => $forecastData['city']['name'] ?? 'Unknown Location',
                'country' => $forecastData['city']['country'] ?? ''
            ]
        ];
    }


    private function findOrCreateLocation($latitude, $longitude, $locationName = null)
    {
        // Check for existing location within a small radius (about 1km)
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
            'latitude' => round($latitude, 6),
            'longitude' => round($longitude, 6)
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

private function calculateStormStatus($precipitation_mm, $chance = null)
{
    if ($chance !== null) {
        if ($chance > 75) return 'severe';
        if ($chance > 35) return 'moderate';
        if ($chance > 0) return 'light';
        return 'none';
    }

    // fallback to mm
    if ($precipitation_mm > 10) return 'severe';
    if ($precipitation_mm > 3) return 'moderate';
    if ($precipitation_mm > 0) return 'light';
    return 'none';
}

}