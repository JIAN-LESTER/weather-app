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


    public function weatherReports()
    {
        return $this->hasMany(WeatherReport::class, 'locID', 'locID');
    }

    public function snapshots()
    {
        return $this->hasMany(Snapshot::class, 'locID', 'locID');
    }
}
