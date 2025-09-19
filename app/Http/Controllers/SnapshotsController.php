<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SnapshotsController extends Controller
{
      public function viewSnapshots(Request $request)
    {

        return view('admin.snapshot_management');
    
    }

        public function viewUserSnapshots(Request $request)
    {

        return view('user.snapshots');
    
    }
}
