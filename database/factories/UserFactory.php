<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'employee_number' => fake()->unique()->numerify('EMP####'),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('ifham#321'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function configure()
    {
        return $this->afterCreating(function ($user) {
            $role = Role::inRandomOrder()->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            $department = Department::inRandomOrder()->first();
            if ($department) {
                $user->department()->associate($department);
                $user->save();
            }

            $submit_application = rand(1, 100) <= 75 ? false : true;

            if ($submit_application) {
                \App\Models\PermitRequestApplication::factory()->create([
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}
