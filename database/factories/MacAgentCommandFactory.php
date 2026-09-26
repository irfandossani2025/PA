<?php

namespace Database\Factories;

use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MacAgentCommand>
 */
class MacAgentCommandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mac_device_id' => MacDevice::factory(),
            'action' => 'open_url',
            'payload' => ['url' => fake()->url()],
            'status' => 'pending',
            'requires_approval' => true,
            'result' => null,
            'claimed_at' => null,
            'completed_at' => null,
        ];
    }
}
