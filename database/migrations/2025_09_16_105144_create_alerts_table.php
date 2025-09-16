<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public $timestamp = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id('alertID');
            $table->foreignId('locID')->references('locID')->on('locations')->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('severity', ['low', 'medium', 'high']);

            $table->timestamp('issued_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
