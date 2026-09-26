<?php

namespace Database\Factories;

use App\Models\MacDevice;
use App\Models\MacTask;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MacTask>
 */
class MacTaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mac_device_id' => MacDevice::factory(),
            'title' => fake()->sentence(3),
            'request' => fake()->sentence(),
            'status' => 'queued',
            'result' => null,
        ];
    }
}
