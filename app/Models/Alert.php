<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $table = 'alerts';
    protected $primaryKey = 'alertID';

    protected $fillable = [
        'locID',
        'alert_type',
        'severity',
        'title',
        'description',
        'recommendations',
        'weather_conditions',
        'is_active',
        'issued_at',
        'expires_at'
    ];

    protected $casts = [
        'recommendations' => 'array',
        'weather_conditions' => 'array',
        'is_active' => 'boolean',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class, 'locID', 'locID');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('expires_at', '>', now());
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('alert_type', $type);
    }

    public function getSeverityColorAttribute()
    {
        return match($this->severity) {
            'extreme' => '#DC2626', // red-600
            'high' => '#EA580C', // orange-600
            'moderate' => '#D97706', // amber-600
            'low' => '#EAB308', // yellow-500
            'info' => '#3B82F6', // blue-500
            default => '#6B7280' // gray-500
        };
    }

    public function getSeverityIconAttribute()
    {
        return match($this->alert_type) {
            'heat' => '🌡️',
            'cold' => '❄️',
            'heavy_rain' => '🌧️',
            'flood_risk' => '🌊',
            'strong_wind' => '💨',
            'storm' => '⛈️',
            default => '⚠️'
        };
    }
}