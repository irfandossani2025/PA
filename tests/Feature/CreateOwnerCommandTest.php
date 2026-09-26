<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOwnerCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_first_owner_account(): void
    {
        $this->artisan('pa:create-owner owner@example.test Irfan')
            ->expectsOutputToContain('PA owner account created.')
            ->assertSuccessful();

        $this->assertTrue(User::query()->sole()->is_owner);
    }

    public function test_it_does_not_replace_an_existing_owner(): void
    {
        User::factory()->create(['is_owner' => true]);

        $this->artisan('pa:create-owner other@example.test Irfan')
            ->expectsOutputToContain('An owner account already exists.')
            ->assertFailed();

        $this->assertSame(1, User::query()->where('is_owner', true)->count());
    }
}
