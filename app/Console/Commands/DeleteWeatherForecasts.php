<?php

namespace App\Console\Commands;

use App\Models\WeatherReport;
use App\Models\Snapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DeleteWeatherForecasts extends Command
{
    protected $signature = 'weather:delete-old-reports {--manual : Run manual deletion mode}';
    protected $description = 'Delete weather reports older than 1 hour (or manual cleanup of all old reports)';

    public function handle()
    {
        if ($this->option('manual')) {
            return $this->manualCleanup();
        }

        return $this->hourlyCleanup();
    }

    private function hourlyCleanup()
    {
        $this->info('Starting hourly cleanup of weather reports...');
        
        // Delete reports older than 1 hour
        $cutoffTime = now()->subHour();
        
        try {
            // Get count before deletion for reporting
            $reportsToDelete = WeatherReport::where('created_at', '<', $cutoffTime)->count();
            
            if ($reportsToDelete === 0) {
                $this->info('No old weather reports to delete (older than 1 hour).');
                return 0;
            }

            // Delete old weather reports (snapshots will cascade delete)
            $deletedReports = WeatherReport::where('created_at', '<', $cutoffTime)->delete();
            
            // Clean up any orphaned snapshots (safety check)
            $orphanedSnapshots = Snapshot::whereDoesntHave('weatherReport')->delete();
            
            $this->info("\n=== Hourly Cleanup Summary ===");
            $this->info("Cutoff time: {$cutoffTime->format('Y-m-d H:i:s')}");
            $this->info("Weather reports deleted: {$deletedReports}");
            
            if ($orphanedSnapshots > 0) {
                $this->info("Orphaned snapshots cleaned: {$orphanedSnapshots}");
            }
            
            $this->info("Hourly cleanup completed successfully!");
            
            Log::info('Weather reports hourly cleanup completed', [
                'deleted_reports' => $deletedReports,
                'orphaned_snapshots' => $orphanedSnapshots,
                'cutoff_time' => $cutoffTime->toISOString()
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error during hourly cleanup: {$e->getMessage()}");
            
            Log::error('Weather reports hourly cleanup failed', [
                'error' => $e->getMessage(),
                'cutoff_time' => $cutoffTime->toISOString(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }

    private function manualCleanup()
    {
        $this->info('Starting manual cleanup of all old weather reports...');
        
        $today = now()->toDateString();
        
        try {
            // Get count before deletion for reporting
            $reportsToDelete = WeatherReport::where('report_date', '<', $today)->count();
            
            if ($reportsToDelete === 0) {
                $this->info('No old weather reports to delete.');
                return 0;
            }

            if (!$this->confirm("Delete {$reportsToDelete} old weather reports? This cannot be undone.")) {
                $this->info('Manual cleanup cancelled.');
                return 0;
            }

            // Delete old weather reports (snapshots will cascade delete)
            $deletedReports = WeatherReport::where('report_date', '<', $today)->delete();
            
            // Clean up any orphaned snapshots (safety check)
            $orphanedSnapshots = Snapshot::whereDoesntHave('weatherReport')->delete();
            
            $this->info("\n=== Manual Cleanup Summary ===");
            $this->info("Weather reports deleted: {$deletedReports}");
            
            if ($orphanedSnapshots > 0) {
                $this->info("Orphaned snapshots cleaned: {$orphanedSnapshots}");
            }
            
            $this->info("Manual cleanup completed successfully!");
            
            Log::info('Weather reports manual cleanup completed', [
                'deleted_reports' => $deletedReports,
                'orphaned_snapshots' => $orphanedSnapshots,
                'kept_date' => $today
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("Error during manual cleanup: {$e->getMessage()}");
            
            Log::error('Weather reports manual cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return 1;
        }
    }
}