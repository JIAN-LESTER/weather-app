<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use Illuminate\Http\Request;

class LogsController extends Controller
{
   public function viewLogs(Request $request)
    {

       $search = $request->get('search');
        $filter = $request->get('filter', 'all'); 
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $logs = Logs::query()->with('user');

        if ($search) {
            if ($filter === 'user') {
                $logs->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            } elseif ($filter === 'action') {
                $logs->where('action', 'like', "%{$search}%");
            } else { 
                $logs->where(function ($query) use ($search) {
                    $query->whereHas('user', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                        ->orWhere('action', 'like', "%{$search}%")
                        ->orWhere('created_at', 'like', "%{$search}%");
                });
            }
        }

        if ($startDate) {
            $logs->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $logs->whereDate('created_at', '<=', $endDate);
        }

        $logs = $logs->orderBy('created_at', 'desc')->paginate(12)->appends($request->query());

        return view('admin.logs', compact('logs', 'search', 'filter'));
    
    }

    

}
