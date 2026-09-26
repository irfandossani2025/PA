<?php

namespace Tests\Feature;

use App\Models\MacAgentCommand;
use App\Models\MacDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacAgentCommandApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_creates_a_pending_https_url_command(): void
    {
        $owner = User::factory()->create(['is_owner' => true]);
        $device = MacDevice::factory()->create();

        $this->actingAs($owner)
            ->post('/commands', [
                'action' => 'open_url',
                'device_id' => $device->id,
                'label' => 'Open PA website',
                'url' => 'https://pa.irfandossani.online',
            ])
            ->assertSessionHas('status');

        $command = MacAgentCommand::query()->sole();

        $this->assertSame('pending', $command->status);
        $this->assertSame('Open PA website', $command->label);
        $this->assertSame(['url' => 'https://pa.irfandossani.online'], $command->payload);
    }

    public function test_owner_approves_a_pending_command(): void
    {
        $owner = User::factory()->create(['is_owner' => true]);
        $command = MacAgentCommand::factory()->create(['status' => 'pending']);

        $this->actingAs($owner)
            ->post('/commands/'.$command->id.'/approve')
            ->assertSessionHas('status');

        $this->assertSame('approved', $command->refresh()->status);
    }

    public function test_non_owner_cannot_create_a_mac_command(): void
    {
        $user = User::factory()->create(['is_owner' => false]);
        $device = MacDevice::factory()->create();

        $this->actingAs($user)
            ->post('/commands', [
                'action' => 'open_application',
                'application' => 'Safari',
                'device_id' => $device->id,
                'label' => 'Open Safari',
            ])
            ->assertForbidden();

        $this->assertSame(0, MacAgentCommand::query()->count());
    }
}
