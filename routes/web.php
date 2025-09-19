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

Route::get('/', [AuthController::class, 'showLoginForm'])->name('home'); // or remove if not needed
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('loginForm'); // <-- this is the fix
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
    // Profile view route
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile.profile');

    // Edit profile form route
    Route::get('/profile/edit/{userID}', [ProfileController::class, 'editProfile'])->name('profile.edit');

    // Update profile route (this is what your form action uses)
    Route::put('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');

    // Alternative route if you prefer POST
    // Route::post('/profile/update', [ProfileController::class, 'updateProfile'])->name('profile.update');
});




Route::post('/weather/store-snapshot', [WeatherController::class, 'storeWeatherSnapshot'])
    ->name('weather.store-snapshot');

Route::get('/weather/location-history/{locID}', [WeatherController::class, 'getLocationWeatherHistory'])
    ->name('weather.location-history');

Route::get('/weather/todays-snapshots', [WeatherController::class, 'getTodaysWeatherSnapshots'])
    ->name('weather.todays-snapshots');

// Optional: Additional utility routes
Route::get('/weather/reports', [WeatherController::class, 'getAllReports'])
    ->name('weather.all-reports');

Route::delete('/weather/snapshot/{snapshotID}', [WeatherController::class, 'deleteSnapshot'])
    ->name('weather.delete-snapshot');