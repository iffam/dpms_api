<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $zones = [
            [
                'name' => 'International Terminal',
                'code' => 'ZONE-A',
                'description' => 'International Terminal area of Velana International Airport',
                'color' => '#FF0000'
            ],
            [
                'name' => 'Domestic Terminal',
                'code' => 'ZONE-B',
                'description' => 'Domestic Terminal area of Velana International Airport',
                'color' => '#00FF00'
            ],
            [
                'name' => 'Cargo Terminal',
                'code' => 'ZONE-C',
                'description' => 'Cargo Terminal area of Velana International Airport',
                'color' => '#0000FF'
            ],
            [
                'name' => 'Seaplane Terminal',
                'code' => 'ZONE-D',
                'description' => 'Seaplane Terminal area of Velana International Airport',
                'color' => '#FFFF00'
            ],
            [
                'name' => 'VIP Terminal',
                'code' => 'ZONE-E',
                'description' => 'VIP Terminal area of Velana International Airport',
                'color' => '#FF00FF'
            ],
            [
                'name' => 'Aircraft Maintenance',
                'code' => 'ZONE-F',
                'description' => 'Aircraft Maintenance area of Velana International Airport',
                'color' => '#00FFFF'
            ],
            [
                'name' => 'Fuel Farm',
                'code' => 'ZONE-G',
                'description' => 'Fuel Farm area of Velana International Airport',
                'color' => '#FFA500'
            ],
            [
                'name' => 'Air Traffic Control',
                'code' => 'ZONE-H',
                'description' => 'Air Traffic Control area of Velana International Airport',
                'color' => '#800080'
            ]
        ];

        \App\Models\Zone::insert($zones);
    }
}
