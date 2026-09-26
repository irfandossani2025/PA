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
        Schema::create('mac_agent_commands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mac_device_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->json('payload')->nullable();
            $table->string('status', 30)->default('pending');
            $table->boolean('requires_approval')->default(true);
            $table->json('result')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['mac_device_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mac_agent_commands');
    }
};
