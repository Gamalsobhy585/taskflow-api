<?php

namespace Tests\Feature\Modules\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RenewPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            route('authentication.renew-password'),
            [
                'old_password' => 'old-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]
        );

        $response->assertOk();

        $user->refresh();

        $this->assertTrue(
            Hash::check('new-password', $user->password)
        );
    }

    public function test_password_change_fails_when_old_password_is_wrong(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            route('authentication.renew-password'),
            [
                'old_password' => 'wrong-password',
                'new_password' => 'new-password',
                'new_password_confirmation' => 'new-password',
            ]
        );

        $response->assertUnauthorized();

        $user->refresh();

        $this->assertTrue(
            Hash::check('old-password', $user->password)
        );
    }
}