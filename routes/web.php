    <?php

    use App\Http\Controllers\AlertController;
    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\EmailVerificationController;
    use App\Http\Controllers\LogsController;
    use App\Http\Controllers\ProfileController;

    use App\Http\Controllers\TwoFactorAuthController;
    use App\Http\Controllers\UserManagementController;
    use App\Http\Controllers\WeatherController;
    use App\Http\Controllers\WeatherReportsController;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\MapsController;

    Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('loginForm');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('registerForm');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

    Route::get('/admin/dashboard', [DashboardController::class, 'viewAdminDashboard'])->name('admin.dashboard');

    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');


    Route::middleware(['auth'])->group(function () {
        Route::get('/map', [MapsController::class, 'show'])->name('map.show');
        Route::get('/weather_reports', [WeatherReportsController::class, 'viewWeatherReports'])->name('weather_reports.show');
        Route::get('/logs', [LogsController::class, 'viewLogs'])->name('logs.show');
        Route::get('/user-management', [UserManagementController::class, 'viewUsers'])->name('admin.user_management');

        Route::get('/user/map', [MapsController::class, 'viewMaps'])->name('user.map.show');
        Route::get('/user/weather_reports', [WeatherReportsController::class, 'viewUserWeatherReports'])->name('user.weather_reports.show');
    });

    Route::prefix('admin/user_crud')->name('admin.')->group(function () {
        Route::get('/create', [UserManagementController::class, 'create'])->name('users-create');
        Route::post('/store', [UserManagementController::class, 'store'])->name('users-store');
        Route::get('/show/{id}', [UserManagementController::class, 'show'])->name('show');
        Route::get('/edit/{id}', [UserManagementController::class, 'edit'])->name('users-edit');
        Route::put('/update/{id}', [UserManagementController::class, 'update'])->name('update');
        Route::delete('/destroy/{id}', [UserManagementController::class, 'destroy'])->name('users-destroy');
    });

    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'profile'])->name('profile.profile');
        Route::get('/profile/edit/{userID}', [ProfileController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    });

    Route::middleware(['auth'])->group(function () {
        // Main weather storage route (JSON-based for full day snapshots)
        Route::post('/weather/store-full-day-snapshots', [WeatherController::class, 'storeFullDayForecastSnapshots'])
            ->name('weather.store-full-day-snapshots');
        
        // Single time period storage (for "Save Current Time" functionality)
        Route::post('/weather/store-current-snapshot', [WeatherController::class, 'storeCurrentTimeSnapshot'])
            ->name('weather.store-current-snapshot');
        
        // Data retrieval routes
        Route::get('/weather/todays-snapshots', [WeatherController::class, 'getTodaysWeatherSnapshots'])
            ->name('weather.todays-snapshots');
        
        Route::get('/weather/location-history/{locID}', [WeatherController::class, 'getLocationWeatherHistory'])
            ->name('weather.location-history');
    });

   

    Route::middleware(['auth'])->group(function () {
        // Weather Reports routes (no admin prefix)
        Route::get('/weather-reports', [WeatherReportsController::class, 'viewWeatherReports'])
            ->name('weather_reports.show');
        
        // Store forecasts NOW (instant storage)
        Route::post('/weather-reports/store-now', [WeatherReportsController::class, 'storeNow'])
            ->name('weather_reports.store_now');
        
        // Manual cleanup endpoint
        Route::post('/weather-reports/cleanup', [WeatherReportsController::class, 'triggerCleanup'])
            ->name('weather_reports.cleanup');
        
        // Delete specific snapshot
        Route::delete('/weather-reports/snapshots/{snapshotID}', [WeatherReportsController::class, 'deleteSnapshot'])
            ->name('weather_reports.delete_snapshot');
    });

    // API endpoints for real-time data
    Route::prefix('api/weather')->group(function () {
        Route::get('/location/{locID}/current/{period?}', [WeatherReportsController::class, 'getRealTimeWeather'])
            ->name('api.weather.current_period');
    });

    // Add this route to your routes/web.php
Route::post('/weather-reports/refresh-all', [WeatherReportsController::class, 'refreshAll'])
    ->name('weather.refresh.all');





// Web routes for alert pages (if you need them)
Route::middleware(['auth'])->group(function () {
    Route::get('/alerts/location/{locID}', function ($locID) {
        $location = \App\Models\Location::findOrFail($locID);
        return view('alerts.location', ['location' => $location]);
    })->name('alerts.location');
});
