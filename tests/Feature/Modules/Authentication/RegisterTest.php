<?php

namespace Tests\Feature\Modules\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Gamal Sobhy',
            'email' => 'gamal@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath(
                'message',
                __('messages.register.success')
            );

        $this->assertDatabaseHas('users', [
            'name' => 'Gamal Sobhy',
            'email' => 'gamal@example.com',
        ]);

        $user = User::query()
            ->where('email', 'gamal@example.com')
            ->firstOrFail();

        $this->assertNotSame(
            'password123',
            $user->password
        );
    }

    public function test_registration_requires_valid_data(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }

    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'gamal@example.com',
        ]);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Another User',
            'email' => 'gamal@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath(
                'message',
                __('messages.register.email_exists')
            );

        $this->assertDatabaseCount('users', 1);
    }
}