<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        // Create personal access client
        Artisan::call('passport:client', [
            '--personal' => true,
            '--name' => 'Personal Access Client'
        ]);

        // Create password grant client
        Artisan::call('passport:client', [
            '--password' => true,
            '--name' => 'Password Grant Client',
            '--provider' => 'users'
        ]);


        Role::create(['name' => 'admin']);
        Role::create(['name' => 'staff']);
        Role::create(['name' => 'security-officer']);

        // Seeders
        $this->call([
            DepartmentSeeder::class,
            ZoneSeeder::class,
        ]);

        // Dummy data
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@dpms.test',
        ])->assignRole('admin');

        $staff = User::factory()->create([
            'name' => 'Staff User',
            'email' => 'staff@dpms.test',
        ])->assignRole('staff');

        $security = User::factory()->create([
            'name' => 'Security User',
            'email' => 'security@dpms.test',
        ])->assignRole('security-officer');


        $this->call([
            PermitRequestApplicationSeeder::class,
            PermitSeeder::class,
        ]);

        User::factory(1000)->create();
    }
}
