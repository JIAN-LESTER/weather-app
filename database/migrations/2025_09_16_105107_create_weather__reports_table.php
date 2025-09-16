<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public $timestamp = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('weather__reports', function (Blueprint $table) {
            $table->id('wrID');
            $table->foreignId('locID')->references('locID')->on('locations')->onDelete('cascade');
            $table->float('temperature');
            $table->boolean('storm');
            $table->timestamp('fetched_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather__reports');
    }
};
