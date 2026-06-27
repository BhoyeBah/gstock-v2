<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name'  => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
    }

    public function test_profile_update_requires_valid_email(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name'  => 'Test User',
                'email' => 'not-an-email',
            ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_profile_update_requires_name(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name'  => '',
                'email' => $user->email,
            ]);

        $response->assertSessionHasErrors('name');
    }
}

