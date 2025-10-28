<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MileageRate;
use Illuminate\Support\Facades\DB;

class MileageRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('mileage_rates')->truncate();

        $mileageRates = [
            // Vehicle Series 1
            ['vehicle_series' => 1, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.5, 'status' => 1],
            ['vehicle_series' => 1, 'load_from' => 5.1, 'load_to' => 20, 'mileage_rate' => 3.0, 'status' => 1],
            ['vehicle_series' => 1, 'load_from' => 20.1, 'load_to' => null, 'mileage_rate' => 2.8, 'status' => 1],

            // Vehicle Series 2
            ['vehicle_series' => 2, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.5, 'status' => 1],
            ['vehicle_series' => 2, 'load_from' => 5.1, 'load_to' => 20, 'mileage_rate' => 3.5, 'status' => 1],
            ['vehicle_series' => 2, 'load_from' => 20.1, 'load_to' => null, 'mileage_rate' => 3.0, 'status' => 1],

            // Vehicle Series 3
            ['vehicle_series' => 3, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.5, 'status' => 1],
            ['vehicle_series' => 3, 'load_from' => 5.1, 'load_to' => 20, 'mileage_rate' => 3.25, 'status' => 1],
            ['vehicle_series' => 3, 'load_from' => 20.1, 'load_to' => null, 'mileage_rate' => 3.0, 'status' => 1],

            // Vehicle Series 4
            ['vehicle_series' => 4, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.5, 'status' => 1],
            ['vehicle_series' => 4, 'load_from' => 5.1, 'load_to' => 20, 'mileage_rate' => 3.25, 'status' => 1],
            ['vehicle_series' => 4, 'load_from' => 20.1, 'load_to' => null, 'mileage_rate' => 3.0, 'status' => 1],

            // Vehicle Series 5
            ['vehicle_series' => 5, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.5, 'status' => 1],
            ['vehicle_series' => 5, 'load_from' => 5.1, 'load_to' => 22, 'mileage_rate' => 3.5, 'status' => 1],
            ['vehicle_series' => 5, 'load_from' => 22.1, 'load_to' => null, 'mileage_rate' => 3.0, 'status' => 1],

            // Vehicle Series 6
            ['vehicle_series' => 6, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.5, 'status' => 1],
            ['vehicle_series' => 6, 'load_from' => 5.1, 'load_to' => 32, 'mileage_rate' => 2.7, 'status' => 1],
            ['vehicle_series' => 6, 'load_from' => 32.1, 'load_to' => null, 'mileage_rate' => 2.35, 'status' => 1],

            // Vehicle Series 7
            ['vehicle_series' => 7, 'load_from' => 0, 'load_to' => 5, 'mileage_rate' => 4.0, 'status' => 1],
            ['vehicle_series' => 7, 'load_from' => 5.1, 'load_to' => 24, 'mileage_rate' => 2.9, 'status' => 1],
            ['vehicle_series' => 7, 'load_from' => 24.1, 'load_to' => 28, 'mileage_rate' => 2.8, 'status' => 1],
            ['vehicle_series' => 7, 'load_from' => 28.1, 'load_to' => null, 'mileage_rate' => 2.6, 'status' => 1],
        ];

        foreach ($mileageRates as $rate) {
            MileageRate::create($rate);
        }

        $this->command->info('Mileage rates seeded successfully!');
    }
}
