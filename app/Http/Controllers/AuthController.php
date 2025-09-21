<?php

namespace App\Http\Controllers;

use App\Mail\TwoFactorCodeMail;
use App\Mail\VerifyEmail;
use App\Models\Logs;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Mail;
use Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(Request $request){
        $validated = $request->validate([
            'email' => 'required|string|email|max:100|unique:users,email',
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user = User::create([
            'userID' => Str::uuid(), 
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

        Mail::to($user->email)->send(new VerifyEmail($user));

        return redirect()->route('loginForm')->with('success', 'Registration successful! Please check your email to verify your account.');
    
    }

     public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();
        if (!$user) {
            return back()->with('error', 'No account found')->withInput();
        }

        if ($this->isAccountLocked($user)) {
            $secondsLeft = now()->diffInSeconds($user->lockout_time);
            $minutesLeft = floor($secondsLeft / 60);
            $secondsRemainder = $secondsLeft % 60;

            return back()
                ->with('account_locked', true)
                ->with('lockout_timer', "$minutesLeft minutes and $secondsRemainder seconds")
                ->withInput();
        }

        if (!Hash::check($request->password, $user->password)) {
            $this->incrementFailedAttempts($user);
            $remainingAttempts = 5 - $user->failed_attempts;

            return back()
                ->with('error', 'Incorrect email or password.')
                ->with('failed_attempts', $user->failed_attempts)
                ->with('remaining_attempts', $remainingAttempts)
                ->withInput();
        }
        $this->resetFailedAttempts($user);

        if ($user->is_verified == 0) {
            return back()->with('not_verified', 'Your email is not yet verified. Please check your email and try again.')->withInput();
        }

        $user->two_factor_code = rand(100000, 999999);
        $user->two_factor_code_expires_at = now()->addMinutes(5);
        $user->save();

        Logs::create([
            'userID' => $user->userID,
            'action' => 'Attempted to login. 2FA code sent.',
            'timestamp' => now(),
        ]);

        Mail::to($user->email)->send(new TwoFactorCodeMail($user));

        session(['2fa_user_id' => $user->userID]);

        return redirect()->route('2fa-form')->with('message', 'A 2FA code has been sent to your email.');
    }


    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

  
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

         
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
        
                $userID = (string) \Illuminate\Support\Str::uuid();

        
                $fullName = explode(' ', $googleUser->getName(), 2);
                $fname = $fullName[0] ?? '';
                $lname = $fullName[1] ?? '';

                $user = User::create([
                    'userID' => $userID,
                    'fname' => $fname,
                    'lname' => $lname,
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => bcrypt(str()->random(16)), 
                    'role' => 'user',
                    'user_status' => 'active',
                    'is_verified' => true,
                ]);
            } else {
            
                if (is_null($user->google_id)) {
                    $user->update([
                        'google_id' => $googleUser->getId(),
                        'avatar' => $googleUser->getAvatar(),
                    ]);
                }
            }

       
       Auth::login($user);

          return redirect()->intended(Auth::user()->role === 'admin' ? route('admin.dashboard') : route('user.dashboard'));

        } catch (\Exception $e) {
            return redirect()->route('login')->with('error', 'Google login failed. Please try again.');
        }
    }


    public function loginGoogleUser($user, $action)
    {
        Auth::login($user);
        
        Logs::create([
            'userID' => $user->userID,
            'action' => $action,
            'timestamp' => now(),
        ]);
    }

    private function redirectAfterGoogleAuth($user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->intended('/dashboard'); // or wherever regular users should go
    }

    /**
     * Extract first name from full name
     */
    private function extractFirstName($fullName)
    {
        $nameParts = explode(' ', trim($fullName));
        return $nameParts[0] ?? '';
    }

    /**
     * Extract last name from full name
     */
    private function extractLastName($fullName)
    {
        $nameParts = explode(' ', trim($fullName));
        array_shift($nameParts); // Remove first name
        return implode(' ', $nameParts);
    }

    protected function isAccountLocked($user)
    {
        if ($user->failed_attempts >= 5) {
            if ($user->lockout_time && now()->lt($user->lockout_time)) {
                Logs::create([
                    'userID' => $user->userID,
                    'action' => "Account locked due to multiple failed login attempts",
                    'timestamp' => now(),
                ]);
                return true;
            }
            $this->resetFailedAttempts($user);
        }

        return false;
    }

    protected function incrementFailedAttempts($user)
    {
        $user->failed_attempts++;
        if ($user->failed_attempts >= 5) {
            $user->lockout_time = now()->addMinutes(5);
            $user->save();
        } else {
            $user->save();
        }
    }

    protected function resetFailedAttempts($user)
    {
        $user->failed_attempts = 0;
        $user->lockout_time = null; 
        $user->save();
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            Logs::create([
                'userID' => $user->userID,
                'action' => "Logged out",
                'timestamp' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function showLoginForm()
    {
        return view('authentication.login');
    }

    public function showRegisterForm()
    {
        return view('authentication.register');
    }
}