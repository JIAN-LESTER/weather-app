<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class TwoFactorAuthController extends Controller
{
        public function twoFactorForm()
    {
        return view('authentication.two-factor');
    }

    public function authenticate(Request $request)
{
    $request->validate([
        'two_factor_code' => 'required|integer|digits:6',
    ]);

    $user = User::find(session('2fa_user_id'));

    if (!$user) {
        return back()->withErrors(['two_factor_code' => 'Session expired. Please log in again.']);
    }

    if (
        $request->input('two_factor_code') == $user->two_factor_code &&
        now()->lt($user->two_factor_code_expires_at)
    ) {
        $user->update([
            'two_factor_code' => null,
            'two_factor_code_expires_at' => null,
        ]);

        Auth::login($user);

        Logs::create([
            'userID' => $user->userID,
            'action' => 'Successful 2FA login',
            'timestamp' => now(),
        ]);

        // 🔹 Check if profile is completed
        if (!$user->isCompleted) {
            // send them to dashboard but trigger modal
            return redirect()
                ->route($user->role === 'admin' ? 'admin.dashboard' : 'user.dashboard')
                ->with('completeProfileModal', true);
        }

        // Normal redirect if profile already completed
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'user') {
            return redirect()->route('user.dashboard');
        }
    }

    Logs::create([
        'userID' => $user->userID,
        'action' => 'Failed 2FA login attempt',
        'timestamp' => now(),
    ]);

    session()->forget('2fa_user_id');

    return back()->withErrors(['two_factor_code' => 'Invalid or expired OTP. Please log in again.']);
}

}
