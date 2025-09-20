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
     * Store forecast snapshots only (4 time periods)
     * Stores forecast data as shown on the map without current weather
     */
    public function storeForecastSnapshots(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'location_name' => 'nullable|string|max:255',
            
            // Forecast data for 4 periods (what's shown in forecast cards)
            'time_slots' => 'required|array',
            'time_slots.morning' => 'nullable|array',
            'time_slots.noon' => 'nullable|array', 
            'time_slots.afternoon' => 'nullable|array',
            'time_slots.evening' => 'nullable|array',
            'time_slots.*.temperature' => 'nullable|numeric|between:-50,60',
            'time_slots.*.rain_amount' => 'nullable|numeric|min:0',
            'time_slots.*.rain_chance' => 'nullable|numeric|between:0,100',
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

            // Process forecast data for 4 periods
            $processedTimeSlots = [];
            $timePeriods = ['morning', 'noon', 'afternoon', 'evening'];

            foreach ($timePeriods as $timeSlot) {
                $data = $request->time_slots[$timeSlot] ?? null;

                if ($data && isset($data['temperature'])) {
                    $rainAmount = $data['rain_amount'] ?? 0;
                    $rainChance = $data['rain_chance'] ?? 0;

                    $processedTimeSlots[$timeSlot] = [
                        'temperature' => round($data['temperature'], 1),
                        'feels_like' => round($data['feels_like'] ?? $data['temperature'], 1),
                        'rain_amount' => round($rainAmount, 2),
                        'rain_chance' => round($rainChance),
                        'storm_status' => $this->calculateStormStatus($rainAmount, $rainChance),
                        'weather_main' => $data['weather_main'] ?? '',
                        'weather_desc' => $data['weather_desc'] ?? '',
                        'weather_icon' => $data['weather_icon'] ?? '',
                        'humidity' => $data['humidity'] ?? 0,
                        'pressure' => $data['pressure'] ?? 0,
                        'wind_speed' => round($data['wind_speed'] ?? 0, 1),
                        'wind_direction' => $data['wind_direction'] ?? 0,
                        'cloudiness' => $data['cloudiness'] ?? 0,
                        'forecast_time' => $data['forecast_time'] ?? null,
                        'recorded_at' => now()->toISOString(),
                    ];
                }
            }

            if (empty($processedTimeSlots)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid forecast data provided for any time period'
                ], 400);
            }

            // Store forecast snapshots only
            $forecastSnapshot = [
                'time_slots' => $processedTimeSlots,
                'snapshot_type' => 'forecast_only',
                'display_format' => 'forecast_cards',
            ];

            // Store forecast snapshot
            $snapshotRow = Snapshot::updateOrCreate(
                [
                    'wrID' => $weatherReport->wrID,
                    'snapshot_time' => 'forecast_' . now()->format('Hi') // e.g., forecast_1430
                ],
                [
                    'snapshots' => $forecastSnapshot,
                    'temperature' => $processedTimeSlots[array_key_first($processedTimeSlots)]['temperature'], // Use first available for querying
                    'storm_status' => $processedTimeSlots[array_key_first($processedTimeSlots)]['storm_status'],
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Forecast snapshots saved successfully',
                'data' => [
                    'location' => [
                        'id' => $location->locID,
                        'name' => $location->name,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                    'weather_report_id' => $weatherReport->wrID,
                    'snapshot_id' => $snapshotRow->snapshotID,
                    'forecast_periods_saved' => array_keys($processedTimeSlots),
                    'total_periods' => count($processedTimeSlots),
                    'snapshot_time' => $snapshotRow->snapshot_time,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Forecast snapshot storage error: ' . $e->getMessage(), [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save forecast snapshots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store full day forecast snapshots (legacy method - kept for compatibility)
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
     * Get today's forecast snapshots
     */
    public function getTodaysForecastSnapshots()
    {
        try {
            $today = now()->toDateString();

            $snapshots = Snapshot::with(['weatherReport.location'])
                ->whereHas('weatherReport', function ($query) use ($today) {
                    $query->where('report_date', $today);
                })
                ->whereNotNull('snapshots')
                ->where('snapshot_time', 'like', 'forecast_%')
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedSnapshots = [];

            foreach ($snapshots as $snapshot) {
                $location = $snapshot->weatherReport->location;
                $snapshotData = $snapshot->snapshots;

                if (!$snapshotData || !is_array($snapshotData) || !isset($snapshotData['time_slots'])) {
                    continue;
                }

                $timeSlots = $snapshotData['time_slots'];

                $formattedSnapshots[] = [
                    'snapshot_id' => $snapshot->snapshotID,
                    'location' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'snapshot_time' => $snapshot->snapshot_time,
                    'snapshot_type' => 'forecast_only',
                    
                    // Forecast periods (forecast cards)
                    'forecast_periods' => $timeSlots,
                    
                    'created_at' => $snapshot->created_at,
                    'recorded_at' => $snapshot->created_at,
                ];
            }

            return response()->json([
                'success' => true,
                'snapshots' => $formattedSnapshots,
                'total_count' => count($formattedSnapshots)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching today\'s forecast snapshots: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch forecast snapshots: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get today's weather snapshots with proper JSON handling (legacy method)
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

                // Handle complete snapshots
                if (isset($snapshotData['current_weather'])) {
                    $currentWeather = $snapshotData['current_weather'];
                    $formattedSnapshots[] = [
                        'snapshot_id' => $snapshot->snapshotID,
                        'location' => $location->name,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'snapshot_time' => 'current',
                        'temperature' => $currentWeather['temperature'] ?? 0,
                        'feels_like' => $currentWeather['feels_like'] ?? 0,
                        'humidity' => $currentWeather['humidity'] ?? 0,
                        'precipitation' => $currentWeather['rain_amount'] ?? 0,
                        'storm_status' => $currentWeather['storm_status'] ?? 'none',
                        'weather_desc' => $currentWeather['weather_desc'] ?? '',
                        'weather_icon' => $currentWeather['weather_icon'] ?? '',
                        'created_at' => $snapshot->created_at,
                        'recorded_at' => $currentWeather['recorded_at'] ?? $snapshot->created_at,
                    ];
                    
                    // Add forecast periods
                    $timeSlots = $snapshotData['time_slots'] ?? [];
                    foreach ($timeSlots as $timeSlot => $data) {
                        $formattedSnapshots[] = [
                            'snapshot_id' => $snapshot->snapshotID,
                            'location' => $location->name,
                            'latitude' => $location->latitude,
                            'longitude' => $location->longitude,
                            'snapshot_time' => $timeSlot,
                            'temperature' => $data['temperature'] ?? 0,
                            'feels_like' => $data['feels_like'] ?? 0,
                            'humidity' => $data['humidity'] ?? 0,
                            'precipitation' => $data['rain_amount'] ?? 0,
                            'storm_status' => $data['storm_status'] ?? 'none',
                            'weather_desc' => $data['weather_desc'] ?? '',
                            'weather_icon' => $data['weather_icon'] ?? '',
                            'created_at' => $snapshot->created_at,
                            'recorded_at' => $data['recorded_at'] ?? $snapshot->created_at,
                        ];
                    }
                } else {
                    // Handle legacy time slot snapshots
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
     * Get weather snapshots for map display
     */
    public function getSnapshotsForMapDisplay()
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

            $mapMarkers = [];

            foreach ($snapshots as $snapshot) {
                $location = $snapshot->weatherReport->location;
                $snapshotData = $snapshot->snapshots;

                if (!$snapshotData || !is_array($snapshotData)) continue;

                // Handle both complete snapshots and time-slot only snapshots
                if (isset($snapshotData['current_weather'])) {
                    // Complete snapshot - use current weather
                    $weatherData = $snapshotData['current_weather'];
                    $markerType = 'current';
                } else {
                    // Time-slot snapshot - use most recent time slot
                    $timeSlots = ['evening', 'afternoon', 'noon', 'morning'];
                    $weatherData = null;
                    $markerType = 'forecast';
                    
                    foreach ($timeSlots as $slot) {
                        if (isset($snapshotData[$slot])) {
                            $weatherData = $snapshotData[$slot];
                            break;
                        }
                    }
                    
                    if (!$weatherData) continue;
                }

                $mapMarkers[] = [
                    'snapshot_id' => $snapshot->snapshotID,
                    'location' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                    'marker_type' => $markerType,
                    'temperature' => $weatherData['temperature'] ?? 0,
                    'storm_status' => $weatherData['storm_status'] ?? 'clear',
                    'rain_chance' => $weatherData['rain_chance'] ?? ($weatherData['precipitation_chance'] ?? 0),
                    'weather_icon' => $weatherData['weather_icon'] ?? '',
                    'weather_desc' => $weatherData['weather_desc'] ?? '',
                    'recorded_at' => $weatherData['recorded_at'] ?? $snapshot->created_at,
                    'created_at' => $snapshot->created_at,
                ];
            }

            return response()->json([
                'success' => true,
                'markers' => $mapMarkers,
                'total_count' => count($mapMarkers)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching map display snapshots: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch map snapshots: ' . $e->getMessage()
            ], 500);
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

            if ($timeSlot) {
                $rain = $forecast['rain']['1h'] ?? 0;
                $precipitation_mm = $rain; // removed snow

                $precipitation_chance = isset($forecast['pop'])
                    ? round($forecast['pop'] * 100, 1)
                    : ($precipitation_mm > 0 ? 100 : 0);

                $timeSlots[$timeSlot] = [
                    'temperature' => round($forecast['main']['temp'], 1),
                    'feels_like' => round($forecast['main']['feels_like'], 1),
                    'precipitation_mm' => round($precipitation_mm, 2),
                    'precipitation_chance' => $precipitation_chance,
                    'storm_status' => $this->calculateStormStatus($precipitation_mm, $precipitation_chance),
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

    /**
     * Enhanced storm status calculation matching frontend logic
     */
    private function calculateStormStatus($rain_amount, $rain_chance = null)
    {
        // Use rain chance as primary indicator (matches frontend logic)
        if ($rain_chance !== null) {
            if ($rain_chance > 80) return 'heavy_rain';
            if ($rain_chance > 60) return 'moderate_rain';
            if ($rain_chance > 30) return 'light_rain';
            if ($rain_chance > 10) return 'possible_rain';
            return 'clear';
        }

        // Fallback to rain amount
        if ($rain_amount > 10) return 'heavy_rain';
        if ($rain_amount > 3) return 'moderate_rain';
        if ($rain_amount > 0.5) return 'light_rain';
        if ($rain_amount > 0) return 'possible_rain';
        return 'clear';
    }

    // Legacy methods for compatibility
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
}