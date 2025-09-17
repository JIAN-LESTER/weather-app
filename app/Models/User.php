<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'userID';
public $incrementing = true;
protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'role',
        'user_status',
        'avatar',
        'is_verified',
        'isCompleted',
        'verification_token',
        'two_factor_code',
        'two_factor_code_expires_at',
        'failed_attempts',
        'lockout_time',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

        public function logs(){
        return $this->hasMany(Logs::class, 'userID', 'userID');
    }

       public function snapshot(){
        return $this->hasMany(Snapshot::class, 'userID', 'userID');
    }



}
