<?php

namespace Tests\Feature\Modules\Authentication;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GetUserInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_information(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('authentication.user-info')
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.id',
                $user->id
            )
            ->assertJsonPath(
                'data.email',
                $user->email
            );
    }

    public function test_guest_cannot_get_user_information(): void
    {
        $response = $this->getJson(
            route('authentication.user-info')
        );

        $response->assertUnauthorized();
    }
}