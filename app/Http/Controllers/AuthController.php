<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Request;
use Str;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'user',
            'user_status' => 'active',
            'verification_token' => Str::random(64),

        ]);

        Logs::create([
            'userID' => $user->userID,
            'action' => 'Registered own account. Email verification sent to '. $user->email. ".",
            'timestamp' => now(),
        ]);

        


    }
}
