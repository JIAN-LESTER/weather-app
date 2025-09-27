<?php

namespace App\Console\Commands;

use App\Models\Location;
use App\Models\WeatherReport;
use App\Models\Snapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class StoreWeatherForecasts extends Command
{
    protected $signature = 'weather:store-forecasts';
    protected $description = 'Store 4-period weather forecasts for all locations';

    public function handle()
    {
        $this->info('Starting automatic weather forecast storage...');
        
        // Get all locations from database
        $locations = Location::all();
        
        if ($locations->isEmpty()) {
            $this->warn('No locations found in database.');
            return 0;
        }

        $successCount = 0;
        $failCount = 0;

        foreach ($locations as $location) {
            try {
                $this->info("Processing: {$location->name} ({$location->latitude}, {$location->longitude})");
                
                // Fetch forecast data from OpenWeatherMap
                $forecastData = $this->fetchForecastData($location->latitude, $location->longitude);
                
                if (!$forecastData) {
                    $this->error("Failed to fetch forecast for {$location->name}");
                    $failCount++;
                    continue;
                }

                // Process and store the forecast
                $this->storeForecastForLocation($location, $forecastData);
                
                $this->info("✓ Successfully stored forecast for {$location->name}");
                $successCount++;
                
            } catch (\Exception $e) {
                $this->error("Error processing {$location->name}: {$e->getMessage()}");
                Log::error("Weather forecast storage error for location {$location->locID}", [
                    'location' => $location->name,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $failCount++;
            }
        }

        $this->info("\n=== Summary ===");
        $this->info("Total locations: " . $locations->count());
        $this->info("Successful: {$successCount}");
        $this->info("Failed: {$failCount}");

        return 0;
    }

    private function fetchForecastData($latitude, $longitude)
    {
        $apiKey = config('services.openweather.key');
        
        if (!$apiKey) {
            throw new \Exception('OpenWeatherMap API key not configured');
        }

        $url = "https://api.openweathermap.org/data/2.5/forecast";
        
        $response = Http::timeout(15)->get($url, [
            'lat' => $latitude,
            'lon' => $longitude,
            'units' => 'metric',
            'appid' => $apiKey
        ]);

        if ($response->failed()) {
            Log::error('OpenWeatherMap API request failed', [
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
            'snapshot_identifier' => 'auto_forecast_' . now()->format('His'),
            'location' => [
                'name' => $location->name,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
            ],
            'time_slots' => $timeSlots,
            'metadata' => [
                'saved_at' => now()->toISOString(),
                'periods_count' => count($timeSlots),
                'source' => 'automated_scheduler',
                'available_periods' => array_keys($timeSlots)
            ]
        ];

        // Check for existing snapshot for today
        $existingSnapshot = Snapshot::where('wrID', $weatherReport->wrID)->first();

        if ($existingSnapshot) {
            $existingData = $existingSnapshot->snapshots ?? [];
            $forecastKey = 'auto_forecast_' . now()->format('His');
            $existingData[$forecastKey] = $snapshotData;
            
            $existingSnapshot->update([
                'snapshots' => $existingData
            ]);
        } else {
            $forecastKey = 'auto_forecast_' . now()->format('His');
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
}