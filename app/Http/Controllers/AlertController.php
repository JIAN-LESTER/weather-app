<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\Location;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AlertController extends Controller
{
    // Alert severity levels
    const SEVERITY_INFO = 'info';
    const SEVERITY_LOW = 'low';
    const SEVERITY_MODERATE = 'moderate';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_EXTREME = 'extreme';

    // Alert types
    const TYPE_HEAT = 'heat';
    const TYPE_COLD = 'cold';
    const TYPE_HEAVY_RAIN = 'heavy_rain';
    const TYPE_FLOOD_RISK = 'flood_risk';
    const TYPE_STRONG_WIND = 'strong_wind';
    const TYPE_STORM = 'storm';

    /**
     * Analyze weather data and generate alerts
     */
    public function analyzeAndGenerateAlerts(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'temperature' => 'required|numeric',
            'rain_amount' => 'required|numeric|min:0',
            'rain_chance' => 'required|numeric|between:0,100',
            'wind_speed' => 'required|numeric|min:0',
            'wind_direction' => 'nullable|numeric|between:0,360',
            'location_name' => 'nullable|string|max:255'
        ]);

        try {
            $location = Location::findOrCreateByCoordinates(
                $request->latitude,
                $request->longitude,
                $request->location_name
            );

            $alerts = $this->generateAlertsFromWeatherData([
                'temperature' => $request->temperature,
                'rain_amount' => $request->rain_amount,
                'rain_chance' => $request->rain_chance,
                'wind_speed' => $request->wind_speed,
                'wind_direction' => $request->wind_direction
            ]);

            $savedAlerts = [];
            foreach ($alerts as $alertData) {
                $alert = Alert::updateOrCreate(
                    [
                        'locID' => $location->locID,
                        'alert_type' => $alertData['type'],
                        'is_active' => true
                    ],
                    [
                        'severity' => $alertData['severity'],
                        'title' => $alertData['title'],
                        'description' => $alertData['description'],
                        'recommendations' => $alertData['recommendations'],
                        'weather_conditions' => $alertData['conditions'],
                        'expires_at' => now()->addHours(6),
                        'issued_at' => now()
                    ]
                );
                $savedAlerts[] = $alert;
            }

            // Deactivate old alerts not in current analysis
            $currentTypes = array_column($alerts, 'type');
            Alert::where('locID', $location->locID)
                ->where('is_active', true)
                ->whereNotIn('alert_type', $currentTypes)
                ->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'alerts' => $savedAlerts,
                'alert_count' => count($savedAlerts),
                'highest_severity' => $this->getHighestSeverity($alerts)
            ]);

        } catch (\Exception $e) {
            \Log::error('Alert generation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate alerts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate alerts based on weather data
     */
    private function generateAlertsFromWeatherData($data)
    {
        $alerts = [];
        $temp = $data['temperature'];
        $rain = $data['rain_amount'];
        $rainChance = $data['rain_chance'];
        $wind = $data['wind_speed'];

        // Temperature alerts
        if ($temp >= 35) {
            $alerts[] = [
                'type' => self::TYPE_HEAT,
                'severity' => $temp >= 40 ? self::SEVERITY_EXTREME : self::SEVERITY_HIGH,
                'title' => $temp >= 40 ? 'Extreme Heat Warning' : 'High Temperature Alert',
                'description' => "Temperature is at {$temp}°C, which poses significant health risks.",
                'recommendations' => [
                    'Stay indoors during peak heat hours (10 AM - 4 PM)',
                    'Drink plenty of water and stay hydrated',
                    'Avoid strenuous outdoor activities',
                    'Check on elderly neighbors and vulnerable individuals',
                    'Never leave children or pets in vehicles'
                ],
                'conditions' => [
                    'temperature' => $temp . '°C',
                    'threshold' => '35°C'
                ]
            ];
        } elseif ($temp <= 10) {
            $alerts[] = [
                'type' => self::TYPE_COLD,
                'severity' => $temp <= 5 ? self::SEVERITY_HIGH : self::SEVERITY_MODERATE,
                'title' => $temp <= 5 ? 'Extreme Cold Warning' : 'Cold Weather Alert',
                'description' => "Temperature has dropped to {$temp}°C. Cold weather precautions advised.",
                'recommendations' => [
                    'Wear warm, layered clothing',
                    'Protect pipes from freezing',
                    'Check heating systems',
                    'Limit time outdoors',
                    'Watch for signs of hypothermia'
                ],
                'conditions' => [
                    'temperature' => $temp . '°C',
                    'threshold' => '10°C'
                ]
            ];
        }

        // Precipitation alerts
        if ($rain > 15 || $rainChance > 80) {
            $severity = $this->calculateRainSeverity($rain, $rainChance);
            $alerts[] = [
                'type' => self::TYPE_HEAVY_RAIN,
                'severity' => $severity,
                'title' => $severity === self::SEVERITY_EXTREME ? 'Extreme Rainfall Warning' : 'Heavy Rain Alert',
                'description' => "Heavy rainfall detected: {$rain}mm/h with {$rainChance}% probability.",
                'recommendations' => [
                    'Avoid unnecessary travel',
                    'Stay away from flood-prone areas',
                    'Monitor local flood warnings',
                    'Secure outdoor items',
                    'Be prepared for power outages'
                ],
                'conditions' => [
                    'rain_amount' => $rain . ' mm/h',
                    'rain_chance' => $rainChance . '%'
                ]
            ];
        }

        if ($rain > 20 || ($rain > 10 && $rainChance > 70)) {
            $alerts[] = [
                'type' => self::TYPE_FLOOD_RISK,
                'severity' => self::SEVERITY_HIGH,
                'title' => 'Flood Risk Warning',
                'description' => 'High rainfall intensity may cause flooding in low-lying areas.',
                'recommendations' => [
                    'Evacuate low-lying areas if advised',
                    'Move valuables to higher ground',
                    'Do not attempt to cross flooded roads',
                    'Keep emergency supplies ready',
                    'Monitor weather updates continuously'
                ],
                'conditions' => [
                    'rain_amount' => $rain . ' mm/h',
                    'rain_chance' => $rainChance . '%'
                ]
            ];
        }

        // Wind alerts
        if ($wind >= 40) {
            $alerts[] = [
                'type' => self::TYPE_STRONG_WIND,
                'severity' => $wind >= 60 ? self::SEVERITY_EXTREME : self::SEVERITY_HIGH,
                'title' => $wind >= 60 ? 'Extreme Wind Warning' : 'Strong Wind Alert',
                'description' => "Wind speeds reaching {$wind} km/h. Potential for property damage.",
                'recommendations' => [
                    'Secure loose outdoor objects',
                    'Stay away from trees and tall structures',
                    'Avoid driving high-profile vehicles',
                    'Close and secure all windows and doors',
                    'Delay outdoor activities'
                ],
                'conditions' => [
                    'wind_speed' => $wind . ' km/h',
                    'threshold' => '40 km/h'
                ]
            ];
        }

        // Combined storm alert
        if (($rain > 10 && $wind > 30) || ($rainChance > 70 && $wind > 40)) {
            $alerts[] = [
                'type' => self::TYPE_STORM,
                'severity' => self::SEVERITY_EXTREME,
                'title' => 'Severe Storm Warning',
                'description' => 'Dangerous combination of heavy rain and strong winds detected.',
                'recommendations' => [
                    'Take shelter immediately',
                    'Stay indoors and away from windows',
                    'Avoid travel unless absolutely necessary',
                    'Unplug electrical appliances',
                    'Have emergency supplies ready',
                    'Follow official evacuation orders'
                ],
                'conditions' => [
                    'rain_amount' => $rain . ' mm/h',
                    'wind_speed' => $wind . ' km/h',
                    'combined_threat' => 'Yes'
                ]
            ];
        }

        return $alerts;
    }

    /**
     * Calculate rain severity based on amount and probability
     */
    private function calculateRainSeverity($amount, $chance)
    {
        if ($amount > 30 || ($amount > 20 && $chance > 90)) {
            return self::SEVERITY_EXTREME;
        }
        if ($amount > 20 || ($amount > 15 && $chance > 80)) {
            return self::SEVERITY_HIGH;
        }
        if ($amount > 10 || $chance > 70) {
            return self::SEVERITY_MODERATE;
        }
        return self::SEVERITY_LOW;
    }

    /**
     * Get highest severity from alerts
     */
    private function getHighestSeverity($alerts)
    {
        $severityLevels = [
            self::SEVERITY_INFO => 1,
            self::SEVERITY_LOW => 2,
            self::SEVERITY_MODERATE => 3,
            self::SEVERITY_HIGH => 4,
            self::SEVERITY_EXTREME => 5
        ];

        $highest = self::SEVERITY_INFO;
        $highestValue = 0;

        foreach ($alerts as $alert) {
            $value = $severityLevels[$alert['severity']] ?? 0;
            if ($value > $highestValue) {
                $highestValue = $value;
                $highest = $alert['severity'];
            }
        }

        return $highest;
    }

    /**
     * Get active alerts for dashboard
     */
    public function getDashboardAlerts()
    {
        try {
            $alerts = Alert::with('location')
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->orderByRaw("FIELD(severity, 'extreme', 'high', 'moderate', 'low', 'info')")
                ->orderBy('issued_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function($alert) {
                    return [
                        'alertID' => $alert->alertID,
                        'alert_type' => $alert->alert_type,
                        'severity' => $alert->severity,
                        'title' => $alert->title,
                        'description' => $alert->description,
                        'recommendations' => $alert->recommendations,
                        'weather_conditions' => $alert->weather_conditions,
                        'issued_at' => $alert->issued_at->toISOString(),
                        'expires_at' => $alert->expires_at->toISOString(),
                        'location' => [
                            'id' => $alert->location->locID,
                            'name' => $alert->location->name,
                            'latitude' => $alert->location->latitude,
                            'longitude' => $alert->location->longitude
                        ]
                    ];
                });

            $summary = [
                'total_alerts' => $alerts->count(),
                'by_severity' => [
                    'extreme' => $alerts->where('severity', self::SEVERITY_EXTREME)->count(),
                    'high' => $alerts->where('severity', self::SEVERITY_HIGH)->count(),
                    'moderate' => $alerts->where('severity', self::SEVERITY_MODERATE)->count(),
                    'low' => $alerts->where('severity', self::SEVERITY_LOW)->count(),
                ],
                'by_type' => [
                    'heat' => $alerts->where('alert_type', self::TYPE_HEAT)->count(),
                    'cold' => $alerts->where('alert_type', self::TYPE_COLD)->count(),
                    'heavy_rain' => $alerts->where('alert_type', self::TYPE_HEAVY_RAIN)->count(),
                    'flood_risk' => $alerts->where('alert_type', self::TYPE_FLOOD_RISK)->count(),
                    'strong_wind' => $alerts->where('alert_type', self::TYPE_STRONG_WIND)->count(),
                    'storm' => $alerts->where('alert_type', self::TYPE_STORM)->count(),
                ]
            ];

            return response()->json([
                'success' => true,
                'alerts' => $alerts->values(),
                'summary' => $summary
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching dashboard alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch alerts',
                'alerts' => [],
                'summary' => [
                    'total_alerts' => 0,
                    'by_severity' => [
                        'extreme' => 0,
                        'high' => 0,
                        'moderate' => 0,
                        'low' => 0,
                    ],
                    'by_type' => [
                        'heat' => 0,
                        'cold' => 0,
                        'heavy_rain' => 0,
                        'flood_risk' => 0,
                        'strong_wind' => 0,
                        'storm' => 0,
                    ]
                ]
            ], 500);
        }
    }

    /**
     * Get alerts for map display
     */
    public function getMapAlerts()
    {
        try {
            $alerts = Alert::with('location')
                ->where('is_active', true)
                ->where('expires_at', '>', now())
                ->get()
                ->groupBy('locID')
                ->map(function ($locationAlerts) {
                    $location = $locationAlerts->first()->location;
                    $highestAlert = $locationAlerts->sortByDesc(function ($alert) {
                        $severityOrder = [
                            'extreme' => 5,
                            'high' => 4,
                            'moderate' => 3,
                            'low' => 2,
                            'info' => 1
                        ];
                        return $severityOrder[$alert->severity] ?? 0;
                    })->first();

                    return [
                        'location_id' => $location->locID,
                        'latitude' => $location->latitude,
                        'longitude' => $location->longitude,
                        'location_name' => $location->name,
                        'alert_count' => $locationAlerts->count(),
                        'highest_severity' => $highestAlert->severity,
                        'primary_alert' => [
                            'type' => $highestAlert->alert_type,
                            'severity' => $highestAlert->severity,
                            'title' => $highestAlert->title,
                            'description' => $highestAlert->description
                        ],
                        'all_alerts' => $locationAlerts->map(function ($alert) {
                            return [
                                'type' => $alert->alert_type,
                                'severity' => $alert->severity,
                                'title' => $alert->title,
                                'description' => $alert->description
                            ];
                        })->values()
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'alert_markers' => $alerts,
                'total_locations' => $alerts->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching map alerts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch map alerts',
                'alert_markers' => [],
                'total_locations' => 0
            ], 500);
        }
    }

    /**
     * Get detailed alert information
     */
    public function getAlertDetails($alertId)
    {
        try {
            $alert = Alert::with('location')->findOrFail($alertId);

            return response()->json([
                'success' => true,
                'alert' => [
                    'alertID' => $alert->alertID,
                    'alert_type' => $alert->alert_type,
                    'severity' => $alert->severity,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'recommendations' => $alert->recommendations,
                    'weather_conditions' => $alert->weather_conditions,
                    'issued_at' => $alert->issued_at->toISOString(),
                    'expires_at' => $alert->expires_at->toISOString(),
                    'location' => [
                        'id' => $alert->location->locID,
                        'name' => $alert->location->name,
                        'latitude' => $alert->location->latitude,
                        'longitude' => $alert->location->longitude
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Alert not found'
            ], 404);
        }
    }

    /**
     * Dismiss an alert
     */
    public function dismissAlert($alertId)
    {
        try {
            $alert = Alert::findOrFail($alertId);
            $alert->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Alert dismissed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to dismiss alert'
            ], 500);
        }
    }
}