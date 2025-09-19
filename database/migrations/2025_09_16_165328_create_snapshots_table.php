<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id('snapshotID');
            $table->foreignId('wrID')->references('wrID')->on('weather_reports')->onDelete('cascade');
            $table->enum('snapshot_time', ['morning','noon','afternoon','evening']);
            
        
            $table->float('temperature');
            $table->float('feels_like');
            $table->integer('humidity');
            $table->integer('pressure');
            $table->float('wind_speed');
            $table->string('wind_direction', 5);
            $table->integer('cloudiness');
            $table->float('precipitation');
            $table->string('weather_main');
            $table->string('weather_desc');
            $table->string('weather_icon');
            $table->enum('storm_status', ['none','light','moderate','severe'])->default('none');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_snapshots');
    }
};
