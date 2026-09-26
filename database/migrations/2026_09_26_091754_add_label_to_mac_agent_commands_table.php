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
        Schema::table('mac_agent_commands', function (Blueprint $table) {
            $table->string('label')->nullable()->after('mac_device_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mac_agent_commands', function (Blueprint $table) {
            $table->dropColumn('label');
        });
    }
};
