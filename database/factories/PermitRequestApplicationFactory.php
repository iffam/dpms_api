<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PermitRequestApplication>
 */
class PermitRequestApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $permit_type = $this->faker->randomElement(['restricted', 'temporary', 'permanent']);
        $active_at = $this->faker->dateTimeBetween('-1 year', '-1 month');

        return [
            'user_id' => \App\Models\User::factory(),
            'permit_type' => $permit_type,
            'active_at' => $permit_type === 'temporary' ? $active_at : null,
            'expired_at' => $permit_type === 'temporary' ? $this->faker->dateTimeBetween($active_at, 'now') : null,
            'zones' => json_encode($this->faker->randomElements(['ZONE-A', 'ZONE-B', 'ZONE-C', 'ZONE-D', 'ZONE-E', 'ZONE-F', 'ZONE-G', 'ZONE-H'], $count = 2)),
            'justification' => $this->faker->text(),
        ];
    }


    public function configure()
    {
        return $this->afterCreating(function ($request) {
            if (rand(0, 1)) {
                $new_request = \App\Models\Permit::factory()->create([
                    'permit_request_application_id' => $request->id,
                    'permit_type' => $request->permit_type,
                    'active_at' => $request->active_at,
                    'expired_at' => $request->expired_at,
                ]);
                $request->status = 'approved';
                $request->save();
                if ($request->zones) {
                    foreach (json_decode($request->zones) as $z) {
                        $zone = \App\Models\Zone::where('code', $z)->first();
                        $new_request->zones()->attach($zone);
                    }
                }
            }
        });
    }
}
