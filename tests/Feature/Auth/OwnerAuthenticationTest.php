<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OwnerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_visitor_is_redirected_to_login(): void
    {
        $this->get('/')
            ->assertRedirectToRoute('login');
    }

    public function test_non_owner_is_forbidden_from_the_dashboard(): void
    {
        $user = User::factory()->create(['is_owner' => false]);

        $this->actingAs($user)
            ->get('/')
            ->assertForbidden();
    }

    public function test_owner_can_sign_in_to_the_dashboard(): void
    {
        $owner = User::factory()->create([
            'email' => 'owner@example.com',
            'is_owner' => true,
            'password' => 'secret-password',
        ]);

        $this->post('/login', [
            'email' => 'owner@example.com',
            'password' => 'secret-password',
        ])
            ->assertRedirectToRoute('dashboard');

        $this->assertAuthenticatedAs($owner);
    }
}
