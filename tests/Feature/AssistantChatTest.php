<?php

namespace Tests\Feature;

use App\Models\MacDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AssistantChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_start_a_safe_mac_task_without_a_second_approval(): void
    {
        config()->set('services.anthropic.key', 'test-key');
        config()->set('services.anthropic.model', 'test-model');
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'content' => [[
                    'type' => 'text',
                    'text' => '{"reply":"I am opening the website on your Mac now.","steps":[{"label":"Open example","action":"open_url","payload":{"url":"https://example.com"}}]}',
                ]],
            ]),
        ]);
        $owner = User::factory()->create(['is_owner' => true]);
        $device = MacDevice::factory()->create();

        $this->actingAs($owner)
            ->post('/chat', ['message' => 'Open example.com'])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('assistant_conversation_messages', [
            'user_id' => $owner->id,
            'role' => 'user',
            'content' => 'Open example.com',
        ]);
        $this->assertDatabaseHas('assistant_conversation_messages', [
            'user_id' => $owner->id,
            'role' => 'assistant',
            'content' => 'I am opening the website on your Mac now.',
        ]);
        $this->assertDatabaseHas('mac_agent_commands', [
            'mac_device_id' => $device->id,
            'action' => 'open_url',
            'label' => 'Open example',
            'status' => 'approved',
            'requires_approval' => false,
        ]);
        Http::assertSent(fn (Request $request): bool => $request->hasHeader('x-api-key'));
    }
}
