<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Snapshot extends Model
{
    use HasFactory;

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


    public function report()
    {
        return $this->belongsTo(Weather_Report::class, 'wrID', 'wrID');
    }
}
