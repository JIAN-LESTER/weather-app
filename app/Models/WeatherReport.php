<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeatherReport extends Model
{
    use HasFactory;


    protected $table = 'weather_reports';
    protected $primaryKey = 'wrID';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'locID',
        'report_date',
    ];


    public function location()
    {
        return $this->belongsTo(Location::class, 'locID', 'locID');
    }


    public function snapshots()
    {
        return $this->hasMany(Snapshot::class, 'wrID', 'wrID');
    }
}
