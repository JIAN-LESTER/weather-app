<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
     public function verifyEmail($token)
    {
        $user = User::where('verification_token', $token)->first();

        if(!$user)
        {
            return redirect('/login')->with('error', 'Invalid Verification Token.');
        }

        $user->is_verified = true;
        $user->verification_token = null;
        $user->save();

        Logs::create([
            'userID' => $user->userID,
            'action' => "Email Verified: ". $user->email. ".",
            'timestamp' => now(),
        ]);

        return redirect('/login')->with('success', 'Email Verified successfully. You can now log in.');
    }
}
