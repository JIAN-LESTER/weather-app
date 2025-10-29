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
        // Test closure - should log every minute
        $schedule->call(function () {
            \Log::info('✓ Scheduler is working! Time: ' . now()->format('Y-m-d H:i:s'));
        })->everyMinute();

        // Store forecasts every minute (for testing)
        $schedule->command('weather:store-forecasts')
            ->everyMinute()
            ->timezone('Asia/Manila')
            ->withoutOverlapping();

        // Delete old reports every minute (for testing)
        $schedule->command('weather:delete-old-reports')
            ->everyMinute()
            ->timezone('Asia/Manila')
            ->withoutOverlapping();
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