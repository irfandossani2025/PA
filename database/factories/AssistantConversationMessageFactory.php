<?php

namespace Database\Factories;

use App\Models\AssistantConversationMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssistantConversationMessage>
 */
class AssistantConversationMessageFactory extends Factory
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
            'role' => 'user',
            'content' => fake()->sentence(),
            'mac_agent_command_id' => null,
        ];
    }
}
