<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('snapshots', function (Blueprint $table) {
            $table->id('snapshotID');
            $table->foreignId('userID')->references('userID')->on('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date');
            $table->enum('snapshot_status', ['pending', 'in-progress', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snapshots');
    }
};
