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
            $table->foreignId('mac_task_id')->nullable()->after('mac_device_id')->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('sequence')->nullable()->after('mac_task_id');
            $table->index(['mac_task_id', 'sequence']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mac_agent_commands', function (Blueprint $table) {
            $table->dropIndex(['mac_task_id', 'sequence']);
            $table->dropConstrainedForeignId('mac_task_id');
            $table->dropColumn('sequence');
        });
    }
};
