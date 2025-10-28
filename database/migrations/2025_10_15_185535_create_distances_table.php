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
        Schema::create('distances', function (Blueprint $table) {
            $table->id();
            $table->string('from_location'); // Starting location
            $table->string('to_location'); // Destination location
            $table->string('trip_name'); // Trip name (FROM-TO format)
            $table->decimal('distance', 8, 2); // Distance in KM (with 2 decimal places)
            $table->enum('status', ['0', '1'])->default('1'); // 0 = inactive, 1 = active
            $table->timestamps();

            // Add indexes for better performance
            $table->index('from_location');
            $table->index('to_location');
            $table->index('trip_name');
            $table->index('status');

            // Add unique constraint for trip routes
            $table->unique(['from_location', 'to_location']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distances');
    }
};
