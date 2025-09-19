<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WeatherReportsController extends Controller
{
     public function viewWeatherReports(Request $request)
    {

        return view('admin.weather_reports');
    
    }

       public function viewUserWeatherReports(Request $request)
    {

        return view('user.weather_reports');
    
    }
}
