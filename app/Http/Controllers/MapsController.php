<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class MapsController extends Controller
{
    public function show()
    {

        $locations = auth()->user()->role === 'admin' ? Location::all() : Location::where('userID', auth()->id())->get();
        return view('admin.maps_management', [
            'googleKey' => config('services.google_maps.key'),
            'openweatherKey' => config('services.openweather.key'),
            'locations' => $locations,
        ]);
    }

    public function viewMaps(Request $request)
    {

         $locations = auth()->user()->role === 'user' ? Location::all() : Location::where('userID', auth()->id())->get();
        return view('user.maps', [
            'googleKey' => config('services.google_maps.key'),
            'openweatherKey' => config('services.openweather.key'),
            'locations' => $locations,
        ]);
    }
}
