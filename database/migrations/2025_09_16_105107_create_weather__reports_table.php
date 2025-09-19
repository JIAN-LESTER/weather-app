<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('weather_reports', function (Blueprint $table) {
            $table->id('wrID');
            $table->foreignId('locID')->references('locID')->on('locations')->onDelete('cascade');
            $table->date('report_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_reports');
    }
};
