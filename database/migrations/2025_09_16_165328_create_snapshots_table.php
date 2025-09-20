<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id('snapshotID');
            $table->foreignId('wrID')->constrained('weather_reports', 'wrID')->onDelete('cascade');
            $table->enum('snapshot_time', ['morning', 'noon', 'afternoon', 'evening']);
            $table->decimal('temperature', 5, 2);
            $table->decimal('feels_like', 5, 2);
            $table->integer('humidity'); // 0-100%
            $table->decimal('pressure', 7, 2); // hPa
            $table->decimal('wind_speed', 5, 2)->default(0); // m/s
            $table->string('wind_direction', 10)->default('0'); // degrees or direction
            $table->integer('cloudiness')->default(0); // 0-100%
            $table->decimal('precipitation', 8, 4)->default(0); // mm
            $table->string('weather_main', 100)->default('');
            $table->string('weather_desc', 255)->default('');
            $table->string('weather_icon', 10)->default('');
            $table->enum('storm_status', ['none', 'light', 'moderate', 'severe'])->default('none');
            $table->timestamps();

            // Unique constraint to prevent duplicate snapshots for same time period
            $table->unique(['wrID', 'snapshot_time']);

            // Indexes for better performance
            $table->index(['wrID', 'snapshot_time']);
            $table->index('storm_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
