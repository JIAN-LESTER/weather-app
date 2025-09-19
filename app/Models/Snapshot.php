<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Snapshot extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'userID',
        'title',
        'description',
        'date',
        'snapshot_status',
    ];


    
    public function user() { return $this->belongsTo(User::class); }
    public function location() { return $this->belongsTo(Location::class); }


}
