<?php

namespace Tests\Feature\Modules\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        User::factory()->create([
            'email' => 'gamal@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(
            route('authentication.login'),
            [
                'email' => 'gamal@example.com',
                'password' => 'password123',
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'message',
                __('messages.login.success')
            )
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user_data',
                ],
            ]);

        $this->assertDatabaseCount(
            'personal_access_tokens',
            1
        );
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'gamal@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson(
            route('authentication.login'),
            [
                'email' => 'gamal@example.com',
                'password' => 'wrong-password',
            ]
        );

        $response
            ->assertUnauthorized()
            ->assertJsonPath(
                'message',
                __('messages.login.invalid_credentials')
            );

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }

    public function test_login_fails_when_user_does_not_exist(): void
    {
        $response = $this->postJson(
            route('authentication.login'),
            [
                'email' => 'missing@example.com',
                'password' => 'password123',
            ]
        );

        $response->assertUnauthorized();

        $this->assertDatabaseCount(
            'personal_access_tokens',
            0
        );
    }
}