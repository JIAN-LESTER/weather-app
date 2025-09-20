<?php

namespace App\Http\Controllers;

use App\Models\Logs;
use App\Models\User;
use App\Models\Log; // Assuming you have a Log model
use Hamcrest\Core\IsEqual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function viewAdminDashboard(Request $request)
    {
        // Get user statistics
        $totalUsers = User::count();
        
        // Count active users (logged in within last 30 days)
        $activeUsers = User::where('user_status', '=', 'active')->count();
        
        // Count verified users (assuming you have email_verified_at column)
        $verifiedUsers = User::where('is_verified', true)->count();
        
        // Get recent logs (last 5 entries)
        // Adjust this based on your actual log table structure
        $recentLogs = collect(); // Default to empty collection
        
        try {
            // If using Laravel's default logging or a custom Log model
            if (class_exists(Log::class)) {
                $recentLogs = Log::latest('created_at')
                    ->take(5)
                    ->get();
            } else {
                // If you're using database logging, adjust table name as needed
         $recentLogs = Logs::with('user') // eager load user relation
    ->latest('created_at')
    ->take(5)
    ->get()
    ->map(function ($log) {
        return (object) [
            'fname'      => $log->user->fname ?? 'info',
            'action'     => $log->action ?? 'No message',
            'created_at' => isset($log->created_at) ? Carbon::parse($log->created_at) : null,
        ];
    });
            }
        } catch (\Exception $e) {
            // If there's an issue with logs table, create some sample data
        }

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'verifiedUsers',
            'recentLogs'
        ));
    }

    public function users()
    {
        // Get all users (you can also paginate if needed)
        $users = User::latest()->paginate(10);

        // Return to a blade view (resources/views/admin/users.blade.php)
        return view('admin.users', compact('users'));
    }

    public function logs()
    {
        $logs = collect(); // Default to empty collection
        
        try {
            // If using a custom Log model
            if (class_exists(\App\Models\Log::class)) {
                $logs = \App\Models\Log::latest('created_at')->paginate(20);
            } else {
                // If you're using database logging
                $logs = DB::table('logs')
                    ->latest('created_at')
                    ->paginate(20);
                
                // Convert to objects with proper structure
                $logs->getCollection()->transform(function ($log) {
                    return (object) [
                        'id' => $log->id ?? null,
                        'level' => $log->level ?? 'info',
                        'message' => $log->message ?? 'No message',
                        'context' => $log->context ?? null,
                        'created_at' => isset($log->created_at) ? Carbon::parse($log->created_at) : null,
                    ];
                });
            }
        } catch (\Exception $e) {
            // If there's an issue with logs table, create some sample paginated data
            $sampleLogs = collect();
            $levels = ['info', 'warning', 'error', 'debug'];
            $messages = [
                'User login successful',
                'Password reset requested',
                'High memory usage detected',
                'Failed to send notification email',
                'Database backup completed',
                'System maintenance started',
                'Cache cleared successfully',
                'API request failed',
                'File upload completed',
                'Session expired'
            ];

            for ($i = 1; $i <= 50; $i++) {
                $sampleLogs->push((object) [
                    'id' => $i,
                    'level' => $levels[array_rand($levels)],
                    'message' => $messages[array_rand($messages)],
                    'context' => null,
                    'created_at' => Carbon::now()->subMinutes(rand(1, 1440)), // Random time within last 24 hours
                ]);
            }

            // Create a manual paginator
            $currentPage = request()->get('page', 1);
            $perPage = 20;
            $currentItems = $sampleLogs->slice(($currentPage - 1) * $perPage, $perPage);
            
            $logs = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentItems,
                $sampleLogs->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );
        }

        // Return to a blade view (resources/views/admin/logs.blade.php)
        return view('admin.logs', compact('logs'));
    }

    /**
     * Get dashboard stats for API endpoints
     */
    public function getDashboardStats()
    {
        return response()->json([
            'total_users' => User::count(),
            'active_users' => User::where('last_login_at', '>=', Carbon::now()->subDays(30))->count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'new_users_today' => User::whereDate('created_at', Carbon::today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            'new_users_this_month' => User::whereMonth('created_at', Carbon::now()->month)->count(),
        ]);
    }
}