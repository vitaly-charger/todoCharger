<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    public function test_guest_sees_login_at_root(): void
    {
        $this->get('/')->assertOk();
        $this->get('/login')->assertOk();
    }

    public function test_protected_routes_redirect_to_login_for_guests(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/tasks')->assertRedirect('/login');
        $this->get('/sources')->assertRedirect('/login');
    }

    public function test_disallowed_user_is_logged_out_and_blocked(): void
    {
        $user = User::factory()->create(['email' => 'someone-else@example.com']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_allowed_user_can_access_dashboard(): void
    {
        config(['inbox.allowed_email' => 'allowed@test.dev']);
        $user = User::factory()->create(['email' => 'allowed@test.dev']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();
    }
}
