<?php

namespace Tests\Unit\Modules\Authentication\DTOs;

use App\Modules\Authentication\DTOs\RegisterUserData;
use PHPUnit\Framework\TestCase;

class RegisterUserDataTest extends TestCase
{
    public function test_it_creates_dto_from_array(): void
    {
        $data = RegisterUserData::fromArray([
            'name' => 'Gamal Sobhy',
            'email' => 'gamal@example.com',
            'password' => 'password123',
        ]);

        $this->assertSame('Gamal Sobhy', $data->name);
        $this->assertSame('gamal@example.com', $data->email);
        $this->assertSame('password123', $data->password);
    }

    public function test_it_converts_dto_to_array(): void
    {
        $data = new RegisterUserData(
            name: 'Gamal Sobhy',
            email: 'gamal@example.com',
            password: 'password123'
        );

        $this->assertSame([
            'name' => 'Gamal Sobhy',
            'email' => 'gamal@example.com',
            'password' => 'password123',
        ], $data->toArray());
    }
}