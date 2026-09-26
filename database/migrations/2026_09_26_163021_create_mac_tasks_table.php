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
        Schema::create('mac_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mac_device_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->longText('request');
            $table->string('status', 30)->default('queued');
            $table->longText('result')->nullable();
            $table->timestamps();

            $table->index(['mac_device_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mac_tasks');
    }
};
