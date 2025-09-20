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

            // JSON column to store all snapshot times
            $table->json('snapshots')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('created_at');
            $table->index('wrID');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
