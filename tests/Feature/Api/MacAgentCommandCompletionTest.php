<?php

namespace Tests\Feature\Api;

use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MacAgentCommandCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_mac_agent_can_complete_its_dispatched_command(): void
    {
        $token = 'test-mac-agent-token';
        $device = MacDevice::factory()->create([
            'token_hash' => hash('sha256', $token),
        ]);
        $command = MacAgentCommand::factory()->for($device, 'device')->create([
            'status' => 'dispatched',
        ]);

        $this->withToken($token)
            ->postJson('/api/mac-agent/commands/'.$command->id.'/complete', [
                'success' => true,
                'result' => ['message' => 'Opened the approved URL.'],
            ])
            ->assertOk()
            ->assertJsonPath('command.id', $command->id)
            ->assertJsonPath('command.status', 'completed');

        $command->refresh();

        $this->assertSame('completed', $command->status);
        $this->assertSame(['message' => 'Opened the approved URL.'], $command->result);
        $this->assertNotNull($command->completed_at);
    }

    public function test_mac_agent_cannot_complete_another_devices_command(): void
    {
        $token = 'test-mac-agent-token';
        $device = MacDevice::factory()->create([
            'token_hash' => hash('sha256', $token),
        ]);
        $otherDevice = MacDevice::factory()->create();
        $command = MacAgentCommand::factory()->for($otherDevice, 'device')->create([
            'status' => 'dispatched',
        ]);

        $this->withToken($token)
            ->postJson('/api/mac-agent/commands/'.$command->id.'/complete', [
                'success' => true,
            ])
            ->assertNotFound();

        $this->assertSame('dispatched', $command->refresh()->status);
        $this->assertNull($command->completed_at);
    }

    public function test_command_completion_requires_a_success_value(): void
    {
        $token = 'test-mac-agent-token';
        $device = MacDevice::factory()->create([
            'token_hash' => hash('sha256', $token),
        ]);
        $command = MacAgentCommand::factory()->for($device, 'device')->create([
            'status' => 'dispatched',
        ]);

        $this->withToken($token)
            ->postJson('/api/mac-agent/commands/'.$command->id.'/complete')
            ->assertInvalid(['success' => 'The success field is required.']);

        $this->assertSame('dispatched', $command->refresh()->status);
        $this->assertNull($command->completed_at);
    }

    public function test_outlook_inbox_completion_creates_an_assistant_summary(): void
    {
        $token = 'test-mac-agent-token';
        $device = MacDevice::factory()->create([
            'token_hash' => hash('sha256', $token),
        ]);
        $command = MacAgentCommand::factory()->for($device, 'device')->create([
            'action' => 'inspect_outlook_inbox',
            'payload' => ['limit' => 12],
            'status' => 'dispatched',
        ]);
        $owner = User::factory()->create(['is_owner' => true]);
        config()->set('services.anthropic.key', 'test-key');
        config()->set('services.anthropic.model', 'test-model');
        Http::preventStrayRequests();
        Http::fake([
            '*' => Http::response([
                'content' => [[
                    'type' => 'text',
                    'text' => 'One visible email appears to need a response.',
                ]],
            ]),
        ]);

        $this->withToken($token)
            ->postJson('/api/mac-agent/commands/'.$command->id.'/complete', [
                'success' => true,
                'result' => [
                    'message' => 'Read the visible Outlook Inbox text for summarization.',
                    'outlook_text' => 'Unread. Alice. Please approve the proposal.',
                ],
            ])
            ->assertOk();

        $this->assertDatabaseHas('assistant_conversation_messages', [
            'user_id' => $owner->id,
            'role' => 'assistant',
            'content' => 'One visible email appears to need a response.',
            'mac_agent_command_id' => $command->id,
        ]);
        Http::assertSentCount(1);
    }
}
