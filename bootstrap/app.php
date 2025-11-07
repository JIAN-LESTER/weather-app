<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withSchedule(function (Schedule $schedule): void {


 $schedule->command('weather:delete-old-reports')
    ->dailyAt('6:00')
    ->timezone('Asia/Manila')
    ->name('delete-old-reports');

$schedule->command('weather:store-forecasts')
    ->dailyAt('6:01')
    ->timezone('Asia/Manila')
    ->name('store-forecasts-morning');

$schedule->command('weather:delete-old-reports')
    ->dailyAt('18:00')
    ->timezone('Asia/Manila')
    ->name('delete-old-reports-evening');

$schedule->command('weather:store-forecasts')
    ->dailyAt('18:01')
    ->timezone('Asia/Manila')
    ->name('store-forecasts-evening');

})
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();