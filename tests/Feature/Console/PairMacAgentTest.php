<?php

namespace Tests\Feature\Console;

use App\Models\MacDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PairMacAgentTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_a_device_with_a_hashed_pairing_token(): void
    {
        $this->artisan('pa:pair-mac', ['name' => 'Irfan MacBook Pro'])
            ->expectsOutputToContain('Mac Agent pairing created.')
            ->expectsOutputToContain('Pairing token (shown once): pa_mac_')
            ->assertSuccessful();

        $device = MacDevice::query()->sole();

        $this->assertSame('Irfan MacBook Pro', $device->name);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $device->getRawOriginal('token_hash'));
    }

    public function test_does_not_create_a_second_pairing_with_the_same_label(): void
    {
        MacDevice::factory()->create(['name' => 'Irfan MacBook Pro']);

        $this->artisan('pa:pair-mac', ['name' => 'Irfan MacBook Pro'])
            ->expectsOutputToContain('A Mac Agent with this label already exists.')
            ->assertFailed();

        $this->assertSame(1, MacDevice::query()->count());
    }
}
