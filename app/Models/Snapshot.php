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
        'snapshot_time',
        'temperature',
        'feels_like',
        'humidity',
        'pressure',
        'wind_speed',
        'wind_direction',
        'cloudiness',
        'precipitation',
        'weather_main',
        'weather_desc',
        'weather_icon',
        'storm_status',
    ];

    protected $casts = [
        'temperature' => 'float',
        'feels_like' => 'float',
        'humidity' => 'integer',
        'pressure' => 'float',
        'wind_speed' => 'float',
        'cloudiness' => 'integer',
        'precipitation' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function weatherReport()
    {
        return $this->belongsTo(WeatherReport::class, 'wrID', 'wrID');
    }

    // Keep the old method for backward compatibility
    public function report()
    {
        return $this->weatherReport();
    }

    // Add storm status calculation as model accessor
    public function getCalculatedStormStatusAttribute()
    {
        $precipitation = $this->precipitation ?? 0;
        
        if ($precipitation > 10) {
            return 'severe';
        } elseif ($precipitation > 3) {
            return 'moderate';
        } elseif ($precipitation > 0) {
            return 'light';
        }
        
        return 'none';
    }

    // Scope for today's snapshots
    public function scopeToday($query)
    {
        return $query->whereHas('weatherReport', function($q) {
            $q->where('report_date', now()->toDateString());
        });
    }

    // Scope for specific time period
    public function scopeByTime($query, $time)
    {
        return $query->where('snapshot_time', $time);
    }
}