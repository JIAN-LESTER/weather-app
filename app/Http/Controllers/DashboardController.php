<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function viewAdminDashboard(Request $request)
    {

        return view('admin.dashboard');

    }
}
