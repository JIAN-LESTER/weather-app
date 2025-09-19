<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Location extends Model
{
    use HasFactory, Notifiable;

        protected $fillable = [
        'name',
        'latitude',
        'longitude',
    ];


     public function snapshots() { return $this->hasMany(Snapshot::class); }
    public function weatherReports() { return $this->hasMany(Weather_Report::class); }
    
}
