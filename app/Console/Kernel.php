<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Store weather forecasts every 15 minutes
        $schedule->command('weather:store-forecasts')
            ->everyMinute()
            ->timezone('Asia/Manila')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Weather forecasts stored successfully - ' . now()->format('Y-m-d H:i:s'));
            })
            ->onFailure(function () {
                \Log::error('Failed to store weather forecasts - ' . now()->format('Y-m-d H:i:s'));
            });

        // Delete old weather reports every 15 minutes
        $schedule->command('weather:delete-old-reports')
            ->everyMinute()
            ->timezone('Asia/Manila')
            ->withoutOverlapping()
            ->onSuccess(function () {
                \Log::info('Old weather reports cleanup completed - ' . now()->format('Y-m-d H:i:s'));
            })
            ->onFailure(function () {
                \Log::error('Failed to delete old weather reports - ' . now()->format('Y-m-d H:i:s'));
            });


        $schedule->command('schedule:list')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}