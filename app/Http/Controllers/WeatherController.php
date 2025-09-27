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

 
      public function storeForecastSnapshots(Request $request)
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
            'time_slots.*.rain_amount' => 'nullable|numeric|min:0',
            'time_slots.*.rain_chance' => 'nullable|numeric|between:0,100',
        ]);

        try {
            // Use improved location finding
            $location = $this->findOrCreateLocation(
                $request->latitude,
                $request->longitude,
                $request->location_name
            );

            $weatherReport = $this->getOrCreateTodaysReport($location->locID);

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

            $snapshotData = [
                'type' => 'forecast',
                'snapshot_type' => 'forecast_periods',
                'snapshot_identifier' => 'forecast_' . now()->format('His'),
                'location' => [
                    'name' => $location->name,
                    'latitude' => $location->latitude,
                    'longitude' => $location->longitude,
                ],
                'time_slots' => $processedTimeSlots,
                'metadata' => [
                    'saved_at' => now()->toISOString(),
                    'periods_count' => count($processedTimeSlots),
                    'source' => 'map_interface',
                    'available_periods' => array_keys($processedTimeSlots)
                ]
            ];

            // Check for existing snapshot for today
            $existingSnapshot = Snapshot::where('wrID', $weatherReport->wrID)->first();

            if ($existingSnapshot) {
                $existingData = $existingSnapshot->snapshots ?? [];
                $forecastKey = 'forecast_' . now()->format('His');
                $existingData[$forecastKey] = $snapshotData;
                
                $existingSnapshot->update([
                    'snapshots' => $existingData
                ]);
                
                $snapshotRow = $existingSnapshot;
            } else {
                $forecastKey = 'forecast_' . now()->format('His');
                $snapshotRow = Snapshot::create([
                    'wrID' => $weatherReport->wrID,
                    'snapshots' => [
                        $forecastKey => $snapshotData
                    ]
                ]);
            }

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
                    'snapshot_key' => $forecastKey,
                    'structure' => 'json_only_schema'
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


    public function getTodaysForecastSnapshots()
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

                foreach ($snapshotData as $key => $data) {
             
                    if (strpos($key, 'forecast_') === 0 && is_array($data)) {
                        
                    
                        if (isset($data['type']) && $data['type'] === 'forecast' && isset($data['time_slots'])) {
                            $timeSlots = $data['time_slots'];
                            $metadata = $data['metadata'] ?? [];
                            
                            $formattedSnapshots[] = [
                                'snapshot_id' => $snapshot->snapshotID,
                                'location' => $location->name,
                                'latitude' => $location->latitude,
                                'longitude' => $location->longitude,
                                'snapshot_time' => $key,
                                'snapshot_type' => 'forecast_periods',
                                'structure_type' => 'structured_json',
                                
                        
                                'forecast_periods' => $timeSlots,
                                'periods_count' => count($timeSlots),
                                'available_periods' => array_keys($timeSlots),
                                
                                'metadata' => $metadata,
                                'created_at' => $snapshot->created_at,
                                'recorded_at' => $metadata['saved_at'] ?? $snapshot->created_at,
                            ];
                        }
                     
                        elseif (isset($data['morning']) || isset($data['noon']) || isset($data['afternoon']) || isset($data['evening'])) {
                            $formattedSnapshots[] = [
                                'snapshot_id' => $snapshot->snapshotID,
                                'location' => $location->name,
                                'latitude' => $location->latitude,
                                'longitude' => $location->longitude,
                                'snapshot_time' => $key,
                                'snapshot_type' => 'forecast_periods',
                                'structure_type' => 'direct_periods',
                     
                                'forecast_periods' => $data,
                                'periods_count' => count($data),
                                'available_periods' => array_keys($data),
                                
                                'created_at' => $snapshot->created_at,
                                'recorded_at' => $snapshot->created_at,
                            ];
                        }
                    }
                }
            }

            return response()->json([
                'success' => true,
                'snapshots' => $formattedSnapshots,
                'total_count' => count($formattedSnapshots),
                'schema_info' => 'JSON-only schema with nested forecast data'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching today\'s forecast snapshots: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch forecast snapshots: ' . $e->getMessage()
            ], 500);
        }
    }


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

          
                foreach ($snapshotData as $key => $data) {
                    if (!is_array($data)) continue;

         
                    if (isset($data['type']) && $data['type'] === 'forecast' && isset($data['time_slots'])) {
                        $timeSlots = $data['time_slots'];
                        
                        foreach ($timeSlots as $timeSlot => $periodData) {
                            $formattedSnapshots[] = [
                                'snapshot_id' => $snapshot->snapshotID,
                                'location' => $location->name,
                                'latitude' => $location->latitude,
                                'longitude' => $location->longitude,
                                'snapshot_time' => $timeSlot,
                                'temperature' => $periodData['temperature'] ?? 0,
                                'feels_like' => $periodData['feels_like'] ?? 0,
                                'humidity' => $periodData['humidity'] ?? 0,
                                'precipitation' => $periodData['rain_amount'] ?? 0,
                                'storm_status' => $periodData['storm_status'] ?? 'none',
                                'weather_desc' => $periodData['weather_desc'] ?? '',
                                'weather_icon' => $periodData['weather_icon'] ?? '',
                                'created_at' => $snapshot->created_at,
                                'recorded_at' => $periodData['recorded_at'] ?? $snapshot->created_at,
                            ];
                        }
                    }
            
                    elseif (in_array($key, ['morning', 'noon', 'afternoon', 'evening'])) {
                        $formattedSnapshots[] = [
                            'snapshot_id' => $snapshot->snapshotID,
                            'location' => $location->name,
                            'latitude' => $location->latitude,
                            'longitude' => $location->longitude,
                            'snapshot_time' => $key,
                            'temperature' => $data['temperature'] ?? 0,
                            'feels_like' => $data['feels_like'] ?? 0,
                            'humidity' => $data['humidity'] ?? 0,
                            'precipitation' => $data['precipitation'] ?? ($data['rain_amount'] ?? 0),
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

                // Look through all entries for the most recent forecast data
                foreach ($snapshotData as $key => $data) {
                    if (!is_array($data)) continue;

                    $weatherData = null;
                    $markerType = 'forecast';

                    // Handle structured forecast format
                    if (isset($data['type']) && $data['type'] === 'forecast' && isset($data['time_slots'])) {
                        $timeSlots = $data['time_slots'];
                        $periods = ['evening', 'afternoon', 'noon', 'morning'];
                        
                        foreach ($periods as $slot) {
                            if (isset($timeSlots[$slot])) {
                                $weatherData = $timeSlots[$slot];
                                break;
                            }
                        }
                    }
                    // Handle direct time period data
                    elseif (in_array($key, ['evening', 'afternoon', 'noon', 'morning'])) {
                        $weatherData = $data;
                    }

                    if ($weatherData) {
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
                        break; // Only need one marker per location
                    }
                }
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
                $precipitation_mm = $rain; 

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
        return Location::findOrCreateByCoordinates(
            $latitude, 
            $longitude, 
            $locationName, 
            0.01 // Tolerance of ~1km
        );
    }


      
    public function getMapLocations(Request $request)
    {
        try {
            $bounds = $request->only(['north', 'south', 'east', 'west']);
            
            $query = Location::withRecentWeather(1); // Locations with weather data from last 24 hours
            
            // Apply bounding box filter if provided
            if (count($bounds) === 4) {
                $query->withinBounds(
                    $bounds['north'],
                    $bounds['south'], 
                    $bounds['east'],
                    $bounds['west']
                );
            }
            
            $locations = $query->with(['snapshots' => function($query) {
                    $query->whereHas('weatherReport', function($subQuery) {
                        $subQuery->where('report_date', now()->toDateString());
                    })->latest();
                }])
                ->get()
                ->map(function($location) {
                    $summary = $location->getLatestWeatherSummary();
                    return [
                        'location_id' => $location->locID,
                        'name' => $location->name,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'weather_summary' => $summary
                    ];
                })
                ->filter(function($location) {
                    return $location['weather_summary'] !== null;
                });

            return response()->json([
                'success' => true,
                'locations' => $locations->values(),
                'total_count' => $locations->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching map locations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch map locations: ' . $e->getMessage()
            ], 500);
        }
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

    private function calculateStormStatus($rain_amount, $rain_chance = null)
    {

        if ($rain_chance !== null) {
            if ($rain_chance > 80) return 'heavy_rain';
            if ($rain_chance > 60) return 'moderate_rain';
            if ($rain_chance > 30) return 'light_rain';
            if ($rain_chance > 10) return 'possible_rain';
            return 'clear';
        }

    
        if ($rain_amount > 10) return 'heavy_rain';
        if ($rain_amount > 3) return 'moderate_rain';
        if ($rain_amount > 0.5) return 'light_rain';
        if ($rain_amount > 0) return 'possible_rain';
        return 'clear';
    }
}