<?php

// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

        protected $primaryKey = 'userID';
        

    protected $fillable = [
        'fname',
        'lname',
        'email',
        'password',
        'google_id',
        'avatar',
        'role',
        'user_status',
        'verification_token',
        'is_verified',
        'email_verified_at',
        'two_factor_code',
        'two_factor_code_expires_at',
        'failed_attempts',
        'lockout_time',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
        'verification_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_code_expires_at' => 'datetime',
        'lockout_time' => 'datetime',
        'is_verified' => 'boolean',
        'failed_attempts' => 'integer',
    ];

    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Check if user has Google authentication
     */
    public function hasGoogleAuth()
    {
        return !is_null($this->google_id);
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute()
    {
        return trim($this->fname . ' ' . $this->lname);
    }
}