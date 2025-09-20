<?php

use App\Http\Controllers\AuthController;
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

Route::get('/verify-email/{token}', [EmailVerificationController::class, 'verifyEmail'])->name('verify.email');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/2fa/form', [TwoFactorAuthController::class, 'twoFactorForm'])->name('2fa-form');
Route::post('/2fa/authenticate', [TwoFactorAuthController::class, 'authenticate'])->name('2fa-authenticate');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');

Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->name('user.dashboard');

// For authenticated users
Route::middleware(['auth'])->group(function () {
    Route::get('/map', [MapsController::class, 'show'])->name('map.show');
    Route::get('/weather_reports', [WeatherReportsController::class, 'viewWeatherReports'])->name('weather_reports.show');
    Route::get('/snapshots', [SnapshotsController::class, 'viewSnapshots'])->name('snapshots.show');
    Route::get('/logs', [LogsController::class, 'viewLogs'])->name('logs.show');
    Route::get('/user-management', [UserManagementController::class, 'viewUsers'])->name('admin.user_management');

    Route::get('/user/map', [MapsController::class, 'viewMaps'])->name('user.map.show');
    Route::get('/user/weather_reports', [WeatherReportsController::class, 'viewUserWeatherReports'])->name('user.weather_reports.show');
    Route::get('/user/snapshots', [SnapshotsController::class, 'viewUserSnapshots'])->name('user.snapshots.show');
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