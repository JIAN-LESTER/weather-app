<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Auth;
use Hash;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function editProfile(string $id)
    {
        $user = Auth::user();  
     
        return view('profile.edit_profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->userID . ',userID',
            'old_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|min:6|confirmed',
        ]);


        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return back()->withErrors(['old_password' => 'The current password is incorrect.'])->withInput();
            }
            $user->password = Hash::make($request->new_password);
        }

    
        $wasIncomplete = !$user->isCompleted;

        $user->fname = $validated['fname'];
        $user->lname = $validated['lname'];
        $user->email = $validated['email'];

    
        if ($this->isComplete($user)) {
            $user->isCompleted = true;
        }

        $user->save();

        Logs::create([
            'userID' => $user->userID,
            'action' => $wasIncomplete ? 
                "Completed profile setup for user: {$user->fname} {$user->lname}" : 
                "Updated profile for user: {$user->fname} {$user->lname}",
            'timestamp' => now(),
        ]);

   
        $message = $wasIncomplete ? 
            'Profile completed successfully! Welcome to the platform.' : 
            'Profile updated successfully!';

        return redirect()->back()->with('success', $message);
    }

    public function profile(Request $request)
    {
        $user = auth()->user();
      
        return view('profile.profile', compact('user'));
    }

    private function isComplete($user)
    {
      
        return !empty($user->fname) && 
               !empty($user->lname) && 
               !empty($user->email);
    }


    public function checkProfileCompletion()
    {
        $user = auth()->user();
        
        if (!$user->isCompleted && !$this->isComplete($user)) {
            return redirect()->back()->with('showProfileModal', true);
        }

        return null; 
    }
}