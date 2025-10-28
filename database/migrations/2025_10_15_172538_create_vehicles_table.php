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
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehical_number')->unique(); // Vehicle number (unique identifier)
            $table->string('company'); // Company name (RRCLPL, RRC, etc.)
            $table->string('vehical_series')->nullable(); // Vehicle series (1, 2, 3, etc.)
            $table->string('fuel_type'); // Fuel type (Diesel, Petrol, Electric, etc.)
            $table->enum('status', ['0', '1'])->default('1'); // 0 = inactive, 1 = active
            $table->timestamps();

            // Add indexes for better performance
            $table->index('company');
            $table->index('fuel_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
