<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            'Air Traffic Control',
            'Terminal Operations',
            'Security',
            'Ground Handling',
            'Aircraft Maintenance',
            'Baggage Services',
            'Customer Service',
            'Airport Maintenance',
            'Emergency Services',
            'Cargo Operations'
        ];

        foreach ($departments as $department) {
            \App\Models\Department::create([
            'name' => $department
            ]);
        }
    }
}
