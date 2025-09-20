<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
   Schema::create('weather_reports', function (Blueprint $table) {
                $table->id('wrID');
                $table->foreignId('locID')->constrained('locations', 'locID')->onDelete('cascade');
                $table->date('report_date');
                $table->timestamps();
                
                // Unique constraint to prevent duplicate reports for same location on same date
                $table->unique(['locID', 'report_date']);
                
                // Index for faster queries
                $table->index(['locID', 'report_date']);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_reports');
    }
};
