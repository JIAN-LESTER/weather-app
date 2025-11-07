<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'locID';

    protected $fillable = [
        'name',
        'latitude',
        'longitude',
    ];

    // Cast coordinates to float for precision
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function weatherReports()
    {
        return $this->hasMany(WeatherReport::class, 'locID', 'locID');
    }

    public function alerts()
{
    return $this->hasMany(Alert::class, 'locID', 'locID');
}

public function activeAlerts()
{
    return $this->alerts()
        ->where('is_active', true)
        ->where('expires_at', '>', now());
}

    /**
     * Get snapshots through weather reports relationship
     */
    public function snapshots()
    {
        return $this->hasManyThrough(
            Snapshot::class,
            WeatherReport::class,
            'locID', // Foreign key on weather_reports table
            'wrID',  // Foreign key on snapshots table
            'locID', // Local key on locations table
            'wrID'   // Local key on weather_reports table
        );
    }

    /**
     * Find existing location within tolerance or create new one
     * Prevents duplicate locations for nearby coordinates
     */
    public static function findOrCreateByCoordinates($latitude, $longitude, $name = null, $tolerance = 0.001)
    {
        // First try to find existing location within tolerance
        $existingLocation = static::where('latitude', '>=', $latitude - $tolerance)
            ->where('latitude', '<=', $latitude + $tolerance)
            ->where('longitude', '>=', $longitude - $tolerance)
            ->where('longitude', '<=', $longitude + $tolerance)
            ->first();

        if ($existingLocation) {
            // Update name if provided and current name is generic
            if ($name && (
                str_contains($existingLocation->name, 'Location (') ||
                $existingLocation->name === 'Unknown Location'
            )) {
                $existingLocation->update(['name' => $name]);
            }
            return $existingLocation;
        }

        // Create new location if none found
        return static::create([
            'name' => $name ?: static::generateLocationName($latitude, $longitude),
            'latitude' => round($latitude, 6),
            'longitude' => round($longitude, 6)
        ]);
    }

    /**
     * Generate a descriptive name for coordinates
     */
    public static function generateLocationName($latitude, $longitude)
    {
        // You can enhance this to use reverse geocoding APIs
        return "Location ({$latitude}, {$longitude})";
    }

    /**
     * Calculate distance between two coordinates in kilometers
     */
    public function distanceTo($latitude, $longitude)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get recent weather snapshots for this location
     */
    public function getRecentSnapshots($days = 7)
    {
        return $this->snapshots()
            ->whereHas('weatherReport', function($query) use ($days) {
                $query->where('report_date', '>=', now()->subDays($days));
            })
            ->with('weatherReport')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get latest weather summary
     */
    public function getLatestWeatherSummary()
    {
        $latestSnapshot = $this->snapshots()
            ->whereHas('weatherReport', function($query) {
                $query->where('report_date', now()->toDateString());
            })
            ->latest()
            ->first();

        return $latestSnapshot ? $latestSnapshot->getSummary() : null;
    }

    /**
     * Scope to find locations within a bounding box
     */
    public function scopeWithinBounds($query, $northLat, $southLat, $eastLng, $westLng)
    {
        return $query->where('latitude', '<=', $northLat)
            ->where('latitude', '>=', $southLat)
            ->where('longitude', '<=', $eastLng)
            ->where('longitude', '>=', $westLng);
    }

    /**
     * Scope to find locations with recent weather data
     */
    public function scopeWithRecentWeather($query, $days = 1)
    {
        return $query->whereHas('snapshots', function($subQuery) use ($days) {
            $subQuery->whereHas('weatherReport', function($reportQuery) use ($days) {
                $reportQuery->where('report_date', '>=', now()->subDays($days));
            });
        });
    }
}