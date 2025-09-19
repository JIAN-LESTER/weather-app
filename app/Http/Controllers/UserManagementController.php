<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{

        public function viewUsers(Request $request)
    {
        $search = $request->get('search');
        $roles = $request->get('roles', []);
        $statuses = $request->get('user_status', []);

        $users = User::query()
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('fname', 'like', "%{$search}%");
                    $q->orWhere('lname', 'like', "%{$search}%");
                });
            })
            ->when(!empty($roles), function ($query) use ($roles) {
                return $query->whereIn('role', $roles);
            })
            ->when(!empty($statuses), function ($query) use ($statuses) {
                return $query->whereIn('user_status', $statuses);
            })
            ->paginate(12)
            ->appends($request->query());

  

        return view('admin.user_management', compact(
            'users',
            'search',
       
            'roles',
            'statuses',

        ));
    }

    public function create(){
        return view('admin.CRUD.add_user');
    }

    public function store(Request $request){
        $validated = $request->validate([
            'fname' => 'required|string|max:255',
            'lname' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
            'role' => 'nullable|in:user,admin',
        ]);


        
        $authUser = Auth::user();
        $user = User::create([
            'fname' => $validated['fname'],
            'lname' => $validated['lname'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'user_status' => 'active',
            'is_verified' => 1,
        ]);

        
        Logs::create([
            'userID' => $authUser->userID,
            'action' => $user->lname . " added a new user: {$validated['lname']}.",
            'timestamp' => now(),
        ]);


        return redirect()->route('admin.user_management');
    }

       public function show(string $id)
    {

        $user = User::findOrFail(($id));
        
        return view('admin.SHOW.user_show', compact('user'));
    }

public function edit($id)
{
    $user = User::findOrFail($id);

    return response()->json([
        'id' => $user->userID,
        'fname' => $user->fname,
        'lname' => $user->lname,
        'email' => $user->email,
        'role' => $user->role,
        'user_status' => $user->user_status,
    ]);
}




    public function update(Request $request, string $id)
{
    // Use userID as the primary key
    $user = User::findOrFail($id);

    $validated = $request->validate([
        'fname' => 'required|string|max:255',
        'lname' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id . ',userID',
        'password' => 'nullable|min:6',
        'role' => 'required|in:user,admin',
        'user_status' => 'nullable|in:active,inactive',
    ]);

    $user->fname = $validated['fname'];
    $user->lname = $validated['lname'];
    $user->email = $validated['email'];

    // Update password if provided
    if (!empty($validated['password'])) {
        $user->password = bcrypt($validated['password']);
    }

    // Role must be updated before checking its effect on year/course
    $user->role = $validated['role'];

   

    $user->user_status = $validated['user_status'];

    $user->save();

    $authUser = Auth::user();

    Logs::create([
        'userID' => $authUser->userID,
        'action' => $user->lname. " updated a user: {$validated['lname']}.",
        'timestamp' => now(),
    ]);

    return redirect()->route('admin.user_management');
}

public function destroy(string $id)
{
    $currentUser = Auth::user();
    $userToDelete = User::where('userID', $id)->firstOrFail(); 

    if ($userToDelete->role === 'admin') {
        return redirect()->route('admin.user_management')
            ->with('error', 'Admin cannot be deleted.');
    }

    if ($userToDelete->userID === $currentUser->userID) { 
        return redirect()->route('admin.user_management')
            ->with('error', 'You cannot delete your own account.');
    }

    $userToDelete->delete();

    Logs::create([
        'userID' => $currentUser->userID,
        'action' => $currentUser->lname . " deleted a user: {$userToDelete->lname}.",
        'timestamp' => now(),
    ]);

    return redirect()->route('admin.user_management')
        ->with('success', 'User deleted successfully');
}







}
