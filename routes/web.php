    <?php

    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\DashboardController;
    use App\Http\Controllers\EmailVerificationController;
    use App\Http\Controllers\LogsController;
    use App\Http\Controllers\ProfileController;
    use App\Http\Controllers\SnapshotsController;
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


    Route::middleware(['auth'])->group(function () {



        Route::prefix('admin')->name('admin.')->middleware(['admin'])->group(function () {
            Route::get('/dashboard', function () {
                return view('admin.dashboard');
            })->name('admin.dashboard');
        });

        Route::prefix('user')->name('user.')->group(function () {
            Route::get('/dashboard', function () {
                return view('user.dashboard');
            })->name('user.dashboard');
        });

    });


        Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    
            Route::get('/users', [DashboardController::class, 'users'])->name('users');
            Route::get('/logs', [DashboardController::class, 'logs'])->name('logs');
        });




    Route::get('/verify-email/{token}', [EmailVerificationController::class, 'verifyEmail'])->name('verify.email');



    Route::get('/2fa/form', [TwoFactorAuthController::class, 'twoFactorForm'])->name('2fa-form');
    Route::post('/2fa/authenticate', [TwoFactorAuthController::class, 'authenticate'])->name('2fa-authenticate');

    Route::get('/admin/dashboard', [DashboardController::class, 'viewAdminDashboard'])->name('admin.dashboard');

    Route::get('/user/dashboard', function () {
        return view('user.dashboard');
    })->name('user.dashboard');

    Route::middleware(['auth'])->group(function () {
        


        
    
        Route::get('/admin/users', [DashboardController::class, 'users'])
            ->name('admin.users');
        

        Route::get('/admin/logs', [DashboardController::class, 'logs'])
            ->name('admin.logs');
        

    });


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

    Route::get('/user-management', [UserManagementController::class, 'viewUsers'])->name('admin.user_management');

    Route::middleware(['auth'])->group(function () {
        Route::get('/profile', [ProfileController::class, 'profile'])->name('profile.profile');
        Route::get('/profile/edit/{userID}', [ProfileController::class, 'editProfile'])->name('profile.edit');
        Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
    });

    // Weather-related routes - Cleaned up and organized
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

    // Public weather API endpoints (no auth required)
    Route::get('/weather/full-day-forecast', [WeatherController::class, 'getFullDayForecastData'])
        ->name('weather.full-day-forecast');

    Route::get('/weather/current-weather', [WeatherController::class, 'getCurrentWeatherData'])
        ->name('weather.current-weather');

    Route::get('/api/weather/point', [WeatherController::class, 'point'])
        ->name('api.weather.point');

        Route::get('/weather/full-day-forecast', [WeatherController::class, 'getFullDayForecastData']);

    Route::middleware(['auth'])->group(function () {
        // Admin weather reports routes
        Route::middleware(['admin'])->prefix('admin')->group(function () {
            Route::get('/weather-reports', [WeatherReportsController::class, 'viewWeatherReports'])
                ->name('weather_reports.show');
            Route::delete('/weather-reports/snapshots/{snapshotID}', [WeatherReportsController::class, 'deleteSnapshot'])
                ->name('weather_reports.delete_snapshot');
        });

        // User weather reports routes
        Route::prefix('user')->group(function () {
            Route::get('/weather-reports/{locID?}', [WeatherReportsController::class, 'viewUserWeatherReports'])
                ->name('user.weather_reports.show');
        });

        // Common weather reports routes
        Route::get('/reports/{wrID}', [WeatherReportsController::class, 'showReport'])
            ->name('reports.show');
        Route::get('/reports/snapshots/{snapshotID}', [WeatherReportsController::class, 'showSnapshot'])
            ->name('reports.snapshot.show');
    });

    // Weather API Routes
    Route::prefix('api/weather')->group(function () {
        Route::get('/point', [WeatherController::class, 'point']);
        Route::get('/current', [WeatherController::class, 'getCurrentWeatherData']);
        Route::get('/forecast', [WeatherController::class, 'getFullDayForecastData']);
        Route::post('/forecast/save', [WeatherController::class, 'storeForecastSnapshots']);
        Route::get('/snapshots/today', [WeatherController::class, 'getTodaysWeatherSnapshots']);
        Route::get('/snapshots/forecast', [WeatherController::class, 'getTodaysForecastSnapshots']);
        Route::get('/snapshots/map', [WeatherController::class, 'getSnapshotsForMapDisplay']);
        Route::get('/locations/map', [WeatherController::class, 'getMapLocations']);
        Route::get('/reports/data', [WeatherReportsController::class, 'getWeatherReportsData']);
        Route::get('/location/{locID}/history/{days?}', [WeatherController::class, 'getLocationWeatherHistory']);
    });


    // Route::middleware(['auth'])->group(function () {
    //     // Weather Reports routes (no admin prefix)
    //     Route::get('/weather-reports', [WeatherReportsController::class, 'viewWeatherReports'])
    //         ->name('weather_reports.show');
        
    //     // Manual cleanup endpoint
    //     Route::post('/weather-reports/cleanup', [WeatherReportsController::class, 'triggerCleanup'])
    //         ->name('weather_reports.cleanup');
        
    //     // Delete specific snapshot
    //     Route::delete('/weather-reports/snapshots/{snapshotID}', [WeatherReportsController::class, 'deleteSnapshot'])
    //         ->name('weather_reports.delete_snapshot');
    // });

    // // API endpoints for real-time data
    // Route::prefix('api/weather')->group(function () {
    //     Route::get('/location/{locID}/current/{period?}', [WeatherReportsController::class, 'getRealTimeWeather'])
    //         ->name('api.weather.current_period');
    // });

    // Add these routes to your web.php file:

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