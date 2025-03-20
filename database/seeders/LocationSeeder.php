<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run()
    {
        $locations = [
            ['city' => 'San Francisco', 'state' => 'California', 'country' => 'USA'],
            ['city' => 'New York', 'state' => 'New York', 'country' => 'USA'],
            ['city' => 'Seattle', 'state' => 'Washington', 'country' => 'USA'],
            ['city' => 'Austin', 'state' => 'Texas', 'country' => 'USA'],
            ['city' => 'Boston', 'state' => 'Massachusetts', 'country' => 'USA'],
            ['city' => 'London', 'state' => 'England', 'country' => 'UK'],
            ['city' => 'Berlin', 'state' => 'Berlin', 'country' => 'Germany'],
            ['city' => 'Toronto', 'state' => 'Ontario', 'country' => 'Canada'],
            ['city' => 'Singapore', 'state' => 'Singapore', 'country' => 'Singapore'],
            ['city' => 'Sydney', 'state' => 'New South Wales', 'country' => 'Australia']
        ];

        foreach ($locations as $location) {
            Location::create($location);
        }
    }
}
