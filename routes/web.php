<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\TwoFactorAuthController;
use Illuminate\Support\Facades\Route;


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