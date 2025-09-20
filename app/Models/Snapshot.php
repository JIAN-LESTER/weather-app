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
        return $this->snapshots[$time] ?? null;
    }

    // Get all time periods with data
    public function getAvailableTimePeriods()
    {
        return array_keys($this->snapshots ?? []);
    }

    // Check if snapshot has data for specific time
    public function hasTimeData($time)
    {
        return isset($this->snapshots[$time]);
    }

    // Get formatted snapshot data for display
    public function getFormattedSnapshots()
    {
        if (!$this->snapshots) {
            return [];
        }

        $formatted = [];
        foreach ($this->snapshots as $time => $data) {
            $formatted[$time] = [
                'temperature' => $data['temperature'] ?? 0,
                'feels_like' => $data['feels_like'] ?? 0,
                'precipitation' => $data['precipitation'] ?? 0,
                'storm_status' => $data['storm_status'] ?? 'none',
                'weather_desc' => $data['weather_desc'] ?? '',
                'weather_icon' => $data['weather_icon'] ?? '',
                'humidity' => $data['humidity'] ?? 0,
                'pressure' => $data['pressure'] ?? 0,
                'wind_speed' => $data['wind_speed'] ?? 0,
                'wind_direction' => $data['wind_direction'] ?? '0',
                'cloudiness' => $data['cloudiness'] ?? 0,
                'weather_main' => $data['weather_main'] ?? '',
            ];
        }

        return $formatted;
    }
}