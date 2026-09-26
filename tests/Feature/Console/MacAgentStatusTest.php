<?php

namespace Tests\Feature\Console;

use App\Models\MacDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MacAgentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_when_no_mac_agents_are_paired(): void
    {
        $this->artisan('pa:mac-agent-status')
            ->expectsOutputToContain('No Mac Agents are paired.')
            ->assertSuccessful();
    }

    public function test_lists_paired_mac_agents_without_their_tokens(): void
    {
        $device = MacDevice::factory()->create([
            'last_seen_at' => null,
            'name' => 'Irfan Mac',
        ]);

        $this->artisan('pa:mac-agent-status')
            ->expectsOutputToContain('Paired Mac Agents:')
            ->expectsOutputToContain("#{$device->id} Irfan Mac — last check-in: Never")
            ->assertSuccessful();
    }
}
