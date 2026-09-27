<?php

namespace Tests\Feature\Api;

use App\Models\MacDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MacAgentChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_paired_mac_can_chat_and_start_an_approved_task_without_a_second_approval(): void
    {
        config()->set('services.anthropic.key', 'test-key');
        config()->set('services.anthropic.model', 'test-model');
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'content' => [[
                    'type' => 'text',
                    'text' => '{"reply":"I am opening Outlook on your Mac now.","steps":[{"label":"Open Outlook","action":"open_application","payload":{"application":"Microsoft Outlook"}}]}',
                ]],
            ]),
        ]);
        $token = 'test-mac-agent-token';
        $owner = User::factory()->create(['is_owner' => true]);
        $device = MacDevice::factory()->create(['token_hash' => hash('sha256', $token)]);

        $this->withToken($token)
            ->postJson('/api/mac-agent/chat', ['message' => 'Open Outlook'])
            ->assertOk()
            ->assertJsonPath('reply', 'I am opening Outlook on your Mac now.');

        $this->assertDatabaseHas('assistant_conversation_messages', [
            'user_id' => $owner->id,
            'role' => 'user',
            'content' => 'Open Outlook',
        ]);
        $this->assertDatabaseHas('assistant_conversation_messages', [
            'user_id' => $owner->id,
            'role' => 'assistant',
            'content' => 'I am opening Outlook on your Mac now.',
        ]);
        $this->assertDatabaseHas('mac_agent_commands', [
            'mac_device_id' => $device->id,
            'action' => 'open_application',
            'label' => 'Open Outlook',
            'status' => 'approved',
            'requires_approval' => false,
        ]);
    }

    public function test_mac_chat_requires_a_valid_paired_device_token(): void
    {
        $this->postJson('/api/mac-agent/chat', ['message' => 'Open Outlook'])
            ->assertUnauthorized();
    }
}
