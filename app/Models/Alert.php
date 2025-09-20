<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Alert extends Model
{
    use HasFactory, Notifiable;

        protected $primaryKey = 'alertID';

    protected $fillable = [
        'locID',
        'title',
        'description',
        'severity',
        'issued_at'
    ];

    
       public function location(){
        return $this->hasMany(Location::class, 'locID', 'locID');
    }
}
