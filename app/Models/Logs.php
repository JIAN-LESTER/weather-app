<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Logs extends Model
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'logID';
    protected $fillable = [
        'userID',
        'action'
    ];

          public function user(){
        return $this->belongsTo(User::class, 'userID', 'userID');
    }
}
