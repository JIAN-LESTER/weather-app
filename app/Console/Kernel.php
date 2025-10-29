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

    \Log::info('🔥 KERNEL SCHEDULE METHOD CALLED!');
    // Test: Simple log every minute
    $schedule->call(function () {
        \Log::info('✅ SCHEDULER IS ALIVE! Time: ' . now()->format('Y-m-d H:i:s'));
        file_put_contents(storage_path('logs/scheduler-proof.txt'), 'Last run: ' . now()->format('Y-m-d H:i:s') . PHP_EOL, FILE_APPEND);
    })->everyMinute()->name('test-scheduler');

    // Store forecasts every minute (for testing) - REMOVED TIMEZONE
    $schedule->command('weather:store-forecasts')
        ->everyMinute()
        ->name('store-forecasts');

    // Delete old reports every minute (for testing) - REMOVED TIMEZONE
    $schedule->command('weather:delete-old-reports')
        ->everyMinute()
        ->name('delete-old-reports');
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