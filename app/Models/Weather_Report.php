<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Weather_Report extends Model
{
    use HasFactory, Notifiable;


    protected $fillable = [
        'locID',
        'temperature',
        'storm',
        'fetched_at'
    ];

    public function location(){
        return $this->hasMany(Location::class, 'locID', 'locID');
    }

}
