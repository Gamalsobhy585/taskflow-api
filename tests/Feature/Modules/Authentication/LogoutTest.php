<?php

namespace Tests\Feature\Modules\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $token = $user
            ->createToken('default_token')
            ->plainTextToken;

        $response = $this
            ->withToken($token)
            ->postJson(route('authentication.logout'));

        $response->assertOk();

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson(
            route('authentication.logout')
        );

        $response->assertUnauthorized();
    }
}