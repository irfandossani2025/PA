<?php

namespace Database\Factories;

use App\Models\ServiceOffering;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceOffering>
 */
class ServiceOfferingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'business_area' => 'it',
            'slug' => fake()->unique()->slug(3),
            'name' => fake()->words(3, true),
            'category' => 'Digital services',
            'starting_price_omr' => 400,
            'price_note' => 'Starting price; scope confirmation required.',
            'summary' => fake()->paragraph(),
            'capabilities' => ['Discovery', 'Delivery'],
            'sales_playbook' => [
                'ideal_for' => ['Growing businesses'],
                'discovery_questions' => ['What outcome do you need?'],
                'qualification_note' => 'Confirm requirements before quoting.',
            ],
            'position' => 1,
            'is_active' => true,
        ];
    }
}
