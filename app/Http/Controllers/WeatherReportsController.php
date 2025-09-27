<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\WeatherReport;
use App\Models\Snapshot;

class WeatherReportsController extends Controller
{
    /**
     * Admin view – list all snapshots with their report & location (9 per page)
     */
    public function viewWeatherReports()
    {
        // Paginate snapshots, 9 per page
        $snapshots = Snapshot::with(['weatherReport.location'])
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        // Get today's snapshots for modal functionality
        $todaySnapshots = $this->getTodaySnapshotsByPeriod();

        return view('admin.weather_reports', [
            'snapshots' => $snapshots,
            'todaySnapshots' => $todaySnapshots,
        ]);
    }

    /**
     * User view – snapshots for a specific location or all (9 per page)
     */
    public function viewUserWeatherReports($locID = null)
    {
        if ($locID) {
            $snapshots = Snapshot::whereHas('weatherReport', function ($q) use ($locID) {
                $q->where('locID', $locID);
            })
                ->with(['weatherReport.location'])
                ->orderBy('created_at', 'desc')
                ->paginate(9);

            $todaySnapshots = $this->getTodaySnapshotsByPeriod();

            return view('user.weather_reports', [
                'snapshots' => $snapshots,
                'todaySnapshots' => $todaySnapshots,
            ]);
        }

        $snapshots = Snapshot::with(['weatherReport.location'])
            ->orderBy('created_at', 'desc')
            ->paginate(9);

        $todaySnapshots = $this->getTodaySnapshotsByPeriod();

        return view('user.weather_reports', [
            'snapshots' => $snapshots,
            'todaySnapshots' => $todaySnapshots,
        ]);
    }

    /**
     * Get today's snapshots organized by location and period for modal functionality
     */
    private function getTodaySnapshotsByPeriod()
    {
        $today = now()->toDateString();
        $snapshots = Snapshot::whereHas('weatherReport', function ($q) use ($today) {
            $q->where('report_date', $today);
        })
        ->with(['weatherReport.location'])
        ->get();

        $organized = [];

        foreach ($snapshots as $snapshot) {
            $location = $snapshot->weatherReport->location;
            if (!$location) continue;

            $locID = $location->locID;
            
            if (!isset($organized[$locID])) {
                $organized[$locID] = [
                    'location' => [
                        'locID' => $location->locID,
                        'name' => $location->name,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                    ],
                    'periods' => [
                        'morning' => null,
                        'noon' => null,
                        'afternoon' => null,
                        'evening' => null
                    ],
                    'raw_snapshots' => []
                ];
            }

            // Store raw snapshot data for modal processing
            $organized[$locID]['raw_snapshots'][] = $snapshot;

            // Extract period data from snapshots
            $snapshotData = $snapshot->snapshots;
            if ($snapshotData && is_array($snapshotData)) {
                foreach ($snapshotData as $key => $data) {
                    if (isset($data['time_slots']) && is_array($data['time_slots'])) {
                        foreach ($data['time_slots'] as $period => $periodData) {
                            // Store the actual period data, not just the snapshot
                            if (isset($organized[$locID]['periods'][$period]) && !$organized[$locID]['periods'][$period]) {
                                $organized[$locID]['periods'][$period] = [
                                    'snapshot' => $snapshot,
                                    'data' => $periodData,
                                    'source_key' => $key
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $organized;
    }

    /**
     * Store forecasts immediately for all locations
     */
    public function storeNow(Request $request)
    {
        try {
            \Log::info('Manual forecast storage triggered');
            
            // Get all locations
            $locations = Location::all();
            
            if ($locations->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No locations found in database'
                ], 404);
            }

            $successCount = 0;
            $failCount = 0;
            $errors = [];

            foreach ($locations as $location) {
                try {
                    // Fetch forecast data
                    $forecastData = $this->fetchForecastData($location->latitude, $location->longitude);
                    
                    if (!$forecastData) {
                        $failCount++;
                        $errors[] = "Failed to fetch forecast for {$location->name}";
                        continue;
                    }

                    // Store the forecast
                    $this->storeForecastForLocation($location, $forecastData);
                    $successCount++;
                    
                } catch (\Exception $e) {
                    $failCount++;
                    $errors[] = "{$location->name}: {$e->getMessage()}";
                    \Log::error("Forecast storage error for location {$location->locID}", [
                        'location' => $location->name,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Forecasts stored: {$successCount} successful, {$failCount} failed",
                'details' => [
                    'total_locations' => $locations->count(),
                    'successful' => $successCount,
                    'failed' => $failCount,
                    'errors' => $errors
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Manual forecast storage error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to store forecasts: ' . $e->getMessage()
            ], 500);
        }
    }

    private function fetchForecastData($latitude, $longitude)
    {
        $apiKey = config('services.openweather.key');
        
        if (!$apiKey) {
            throw new \Exception('OpenWeatherMap API key not configured');
        }

        $url = "https://api.openweathermap.org/data/2.5/forecast";
        
        $response = \Http::timeout(15)->get($url, [
            'lat' => $latitude,
            'lon' => $longitude,
            'units' => 'metric',
            'appid' => $apiKey
        ]);

        if ($response->failed()) {
            \Log::error('OpenWeatherMap API request failed', [
                'status' => $response->status(),
                'lat' => $latitude,
                'lon' => $longitude
            ]);
            return null;
        }

        return $response->json();
    }

    private function storeForecastForLocation($location, $forecastData)
    {
        if (!isset($forecastData['list']) || empty($forecastData['list'])) {
            throw new \Exception('Invalid forecast data structure');
        }

        // Process forecast into 4 time periods
        $timeSlots = $this->processForecastData($forecastData);

        // Get or create today's weather report
        $weatherReport = WeatherReport::firstOrCreate(
            [
                'locID' => $location->locID,
                'report_date' => now()->toDateString()
            ]
        );

        // Prepare snapshot data
        $snapshotData = [
            'type' => 'forecast',
            'snapshot_type' => 'forecast_periods',
            'snapshot_identifier' => 'instant_forecast_' . now()->format('His'),
            'location' => [
                'name' => $location->name,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
            ],
            'time_slots' => $timeSlots,
            'metadata' => [
                'saved_at' => now()->toISOString(),
                'periods_count' => count($timeSlots),
                'source' => 'instant_storage',
                'available_periods' => array_keys($timeSlots)
            ]
        ];

        // Check for existing snapshot for today
        $existingSnapshot = Snapshot::where('wrID', $weatherReport->wrID)->first();

        if ($existingSnapshot) {
            $existingData = $existingSnapshot->snapshots ?? [];
            $forecastKey = 'instant_forecast_' . now()->format('His');
            $existingData[$forecastKey] = $snapshotData;
            
            $existingSnapshot->update([
                'snapshots' => $existingData
            ]);
        } else {
            $forecastKey = 'instant_forecast_' . now()->format('His');
            Snapshot::create([
                'wrID' => $weatherReport->wrID,
                'snapshots' => [
                    $forecastKey => $snapshotData
                ]
            ]);
        }
    }

    private function processForecastData($forecastData)
    {
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

            // Map hours to time periods
            if ($hour >= 6 && $hour <= 10 && !$timeSlots['morning']) {
                $timeSlot = 'morning';
            } elseif ($hour >= 11 && $hour <= 14 && !$timeSlots['noon']) {
                $timeSlot = 'noon';
            } elseif ($hour >= 15 && $hour <= 17 && !$timeSlots['afternoon']) {
                $timeSlot = 'afternoon';
            } elseif ($hour >= 18 && $hour <= 22 && !$timeSlots['evening']) {
                $timeSlot = 'evening';
            }

            if ($timeSlot) {
                $rain = $forecast['rain']['1h'] ?? 0;
                $precipitation_chance = isset($forecast['pop']) 
                    ? round($forecast['pop'] * 100, 1) 
                    : ($rain > 0 ? 100 : 0);

                $timeSlots[$timeSlot] = [
                    'temperature' => round($forecast['main']['temp'], 1),
                    'feels_like' => round($forecast['main']['feels_like'], 1),
                    'rain_amount' => round($rain, 2),
                    'rain_chance' => $precipitation_chance,
                    'storm_status' => $this->calculateStormStatus($rain, $precipitation_chance),
                    'weather_main' => $forecast['weather'][0]['main'] ?? '',
                    'weather_desc' => $forecast['weather'][0]['description'] ?? '',
                    'weather_icon' => $forecast['weather'][0]['icon'] ?? '',
                    'humidity' => $forecast['main']['humidity'] ?? 0,
                    'pressure' => $forecast['main']['pressure'] ?? 0,
                    'wind_speed' => round($forecast['wind']['speed'] ?? 0, 1),
                    'wind_direction' => $forecast['wind']['deg'] ?? 0,
                    'cloudiness' => $forecast['clouds']['all'] ?? 0,
                    'forecast_time' => $forecast['dt_txt'],
                    'recorded_at' => now()->toISOString(),
                ];
            }

            // Break if all slots filled
            if (!in_array(null, $timeSlots, true)) {
                break;
            }
        }

        // Filter out null slots
        return array_filter($timeSlots);
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

    /**
     * Show snapshots of a specific report
     */
    public function showReport($wrID)
    {
        $snapshots = Snapshot::with(['weatherReport.location'])
            ->where('wrID', $wrID)
            ->get();

        return view('reports.show', [
            'snapshots' => $snapshots,
        ]);
    }

    /**
     * Show a specific snapshot with formatted data
     */
    public function showSnapshot($snapshotID)
    {
        $snapshot = Snapshot::with(['weatherReport.location'])
            ->findOrFail($snapshotID);

        $formatted = $snapshot->getFormattedSnapshots();

        return view('reports.snapshot_show', [
            'snapshot'  => $snapshot,
            'formatted' => $formatted,
        ]);
    }

    /**
     * Get real-time weather data for a specific location and period
     */
    public function getRealTimeWeather($locID, $period = null)
    {
        $todaySnapshots = $this->getTodaySnapshotsByPeriod();
        
        if (!isset($todaySnapshots[$locID])) {
            return response()->json([
                'error' => 'No weather data available for this location'
            ], 404);
        }
        
        $locationData = $todaySnapshots[$locID];
        
        if ($period && isset($locationData['periods'][$period])) {
            $snapshot = $locationData['periods'][$period];
            return response()->json([
                'location' => $locationData['location'],
                'period' => $period,
                'snapshot' => $snapshot,
                'summary' => $snapshot ? $snapshot->getSummary() : null
            ]);
        }
        
        // Return all periods
        return response()->json([
            'location' => $locationData['location'],
            'periods' => $locationData['periods']
        ]);
    }

    /**
     * API endpoint to manually trigger cleanup
     */
    public function triggerCleanup()
    {
        try {
            $today = now()->toDateString();
            
            // Delete old weather reports (snapshots will cascade delete)
            $deletedReports = WeatherReport::where('report_date', '<', $today)->delete();
            
            // Clean up any orphaned snapshots
            $orphanedSnapshots = Snapshot::whereDoesntHave('weatherReport')->delete();
            
            return response()->json([
                'success' => true,
                'deleted_count' => $deletedReports,
                'orphaned_snapshots' => $orphanedSnapshots,
                'message' => "Successfully deleted {$deletedReports} old weather reports and {$orphanedSnapshots} orphaned snapshots"
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Manual cleanup error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a specific snapshot
     */
    public function deleteSnapshot($snapshotID)
    {
        try {
            $snapshot = Snapshot::findOrFail($snapshotID);
            $snapshot->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Snapshot deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete snapshot: ' . $e->getMessage()
            ], 500);
        }
    }
}