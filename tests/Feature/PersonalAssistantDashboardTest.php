<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonalAssistantDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_the_private_assistant_control_centre(): void
    {
        $owner = User::factory()->create(['is_owner' => true]);

        $this->actingAs($owner)
            ->get('/')
            ->assertOk()
            ->assertSee('IRFAN PA')
            ->assertSee('How can I help?')
            ->assertSee('Message IRFAN PA');
    }
}
