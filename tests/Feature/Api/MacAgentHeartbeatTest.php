<?php

namespace Tests\Feature\Api;

use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacAgentHeartbeatTest extends TestCase
{
    use RefreshDatabase;

    public function test_heartbeat_requires_a_valid_mac_agent_token(): void
    {
        $this->postJson('/api/mac-agent/heartbeat')
            ->assertUnauthorized();

        $this->withToken('not-a-real-token')
            ->postJson('/api/mac-agent/heartbeat')
            ->assertUnauthorized();
    }

    public function test_heartbeat_records_status_and_returns_only_approved_commands(): void
    {
        $token = 'test-mac-agent-token';
        $device = MacDevice::factory()->create([
            'name' => 'Irfan MacBook Pro',
            'token_hash' => hash('sha256', $token),
        ]);

        $approvedCommand = MacAgentCommand::factory()->for($device, 'device')->create([
            'action' => 'open_url',
            'payload' => ['url' => 'https://example.com'],
            'status' => 'approved',
        ]);

        MacAgentCommand::factory()->for($device, 'device')->create([
            'action' => 'open_url',
            'status' => 'pending',
        ]);

        $this->withToken($token)
            ->postJson('/api/mac-agent/heartbeat', [
                'status' => [
                    'agent_version' => '0.1.0',
                    'hostname' => 'Irfan-MacBook-Pro.local',
                    'platform' => 'macOS',
                ],
            ])
            ->assertOk()
            ->assertJsonPath('device.id', $device->id)
            ->assertJsonPath('device.name', 'Irfan MacBook Pro')
            ->assertJsonCount(1, 'commands')
            ->assertJsonPath('commands.0.id', $approvedCommand->id)
            ->assertJsonPath('commands.0.action', 'open_url')
            ->assertJsonPath('commands.0.payload.url', 'https://example.com');

        $device->refresh();

        $this->assertNotNull($device->last_seen_at);
        $this->assertSame('0.1.0', $device->last_status['agent_version']);
        $this->assertSame('dispatched', $approvedCommand->refresh()->status);
        $this->assertNotNull($approvedCommand->claimed_at);
    }
}
