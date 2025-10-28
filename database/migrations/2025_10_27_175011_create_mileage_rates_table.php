<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('mileage_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('vehicle_series')->comment('1-7 vehicle series types');
            $table->decimal('load_from', 10, 2)->comment('Minimum load in MT');
            $table->decimal('load_to', 10, 2)->nullable()->comment('Maximum load in MT (null means unlimited)');
            $table->decimal('mileage_rate', 10, 2)->comment('Mileage rate in km/liter');
            $table->tinyInteger('status')->default(1)->comment('0=Inactive, 1=Active');
            $table->timestamps();

            // Unique constraint to prevent duplicate ranges for same series
            $table->unique(['vehicle_series', 'load_from', 'load_to'], 'unique_series_load_range');
            $table->index('vehicle_series');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mileage_rates');
    }
};
