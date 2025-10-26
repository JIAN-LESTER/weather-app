
<?php

// Migration: database/migrations/xxxx_xx_xx_create_alerts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id('alertID');
            $table->unsignedBigInteger('locID');
            $table->string('alert_type', 50); // heat, cold, heavy_rain, flood_risk, strong_wind, storm
            $table->string('severity', 20); // info, low, moderate, high, extreme
            $table->string('title');
            $table->text('description');
            $table->json('recommendations')->nullable();
            $table->json('weather_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('issued_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->foreign('locID')->references('locID')->on('locations')->onDelete('cascade');
            $table->index(['locID', 'is_active', 'expires_at']);
            $table->index(['severity', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('alerts');
    }
};
