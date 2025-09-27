<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    use HasFactory;

    protected $table = 'snapshots';
    protected $primaryKey = 'snapshotID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'wrID',
        'snapshots',
    ];

    protected $casts = [
        'snapshots' => 'array', // JSON cast to array
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function weatherReport()
    {
        return $this->belongsTo(WeatherReport::class, 'wrID', 'wrID');
    }

    // Scope for today's snapshots
    public function scopeToday($query)
    {
        return $query->whereHas('weatherReport', function($q) {
            $q->where('report_date', now()->toDateString());
        });
    }

    // Get snapshot for a specific time period
    public function getSnapshotForTime($time)
    {
        if (!$this->snapshots) return null;
        
        // Handle nested forecast structure
        foreach ($this->snapshots as $key => $data) {
            if (is_array($data)) {
                // Check if this is a structured forecast with time_slots
                if (isset($data['time_slots']) && isset($data['time_slots'][$time])) {
                    return $data['time_slots'][$time];
                }
                // Check if this is direct time period data
                elseif ($key === $time) {
                    return $data;
                }
            }
        }
        
        return null;
    }

    // Get all time periods with data
    public function getAvailableTimePeriods()
    {
        if (!$this->snapshots) return [];
        
        $periods = [];
        
        foreach ($this->snapshots as $key => $data) {
            if (is_array($data)) {
                // Handle structured forecast
                if (isset($data['time_slots'])) {
                    $periods = array_merge($periods, array_keys($data['time_slots']));
                }
                // Handle direct period data
                elseif (in_array($key, ['morning', 'noon', 'afternoon', 'evening'])) {
                    $periods[] = $key;
                }
            }
        }
        
        return array_unique($periods);
    }

    // Check if snapshot has data for specific time
    public function hasTimeData($time)
    {
        return $this->getSnapshotForTime($time) !== null;
    }

    // Get formatted snapshot data for display - consistent with WeatherController
    public function getFormattedSnapshots()
    {
        if (!$this->snapshots) {
            return [];
        }

        $formatted = [];
        
        foreach ($this->snapshots as $key => $data) {
            if (!is_array($data)) continue;

            // Handle structured forecast format (consistent with WeatherController)
            if (isset($data['type']) && $data['type'] === 'forecast' && isset($data['time_slots'])) {
                foreach ($data['time_slots'] as $timeSlot => $periodData) {
                    $formatted[$timeSlot] = $this->formatPeriodData($periodData);
                }
            }
            // Handle direct time period data
            elseif (in_array($key, ['morning', 'noon', 'afternoon', 'evening'])) {
                $formatted[$key] = $this->formatPeriodData($data);
            }
        }

        return $formatted;
    }

    // Helper method to format individual period data consistently
    private function formatPeriodData($data)
    {
        return [
            'temperature' => $data['temperature'] ?? 0,
            'feels_like' => $data['feels_like'] ?? ($data['temperature'] ?? 0),
            'precipitation' => $data['rain_amount'] ?? ($data['precipitation'] ?? 0),
            'rain_chance' => $data['rain_chance'] ?? ($data['precipitation_chance'] ?? 0),
            'storm_status' => $data['storm_status'] ?? 'clear',
            'weather_desc' => $data['weather_desc'] ?? '',
            'weather_main' => $data['weather_main'] ?? '',
            'weather_icon' => $data['weather_icon'] ?? '',
            'humidity' => $data['humidity'] ?? 0,
            'pressure' => $data['pressure'] ?? 0,
            'wind_speed' => $data['wind_speed'] ?? 0,
            'wind_direction' => $data['wind_direction'] ?? 0,
            'cloudiness' => $data['cloudiness'] ?? 0,
            'forecast_time' => $data['forecast_time'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? null,
        ];
    }

    // Get latest weather data for map display
    public function getLatestWeatherData()
    {
        $periods = ['evening', 'afternoon', 'noon', 'morning'];
        
        foreach ($periods as $period) {
            $data = $this->getSnapshotForTime($period);
            if ($data) {
                return $this->formatPeriodData($data);
            }
        }
        
        return null;
    }

    // Get summary for display
    public function getSummary()
    {
        $latest = $this->getLatestWeatherData();
        if (!$latest) return null;

        return [
            'temperature' => $latest['temperature'],
            'weather_desc' => $latest['weather_desc'],
            'storm_status' => $latest['storm_status'],
            'periods_count' => count($this->getAvailableTimePeriods()),
            'available_periods' => $this->getAvailableTimePeriods()
        ];
    }
}