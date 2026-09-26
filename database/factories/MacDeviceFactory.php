<?php

namespace Database\Factories;

use App\Models\MacDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MacDevice>
 */
class MacDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'token_hash' => hash('sha256', fake()->uuid()),
            'last_status' => null,
            'last_seen_at' => null,
        ];
    }
}
