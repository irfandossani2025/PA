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
        Schema::create('service_offerings', function (Blueprint $table) {
            $table->id();
            $table->string('business_area', 40);
            $table->string('slug')->unique();
            $table->string('name', 160);
            $table->string('category', 100);
            $table->decimal('starting_price_omr', 12, 3)->nullable();
            $table->string('price_note', 255)->nullable();
            $table->text('summary');
            $table->longText('capabilities');
            $table->longText('sales_playbook');
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['business_area', 'is_active', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_offerings');
    }
};
