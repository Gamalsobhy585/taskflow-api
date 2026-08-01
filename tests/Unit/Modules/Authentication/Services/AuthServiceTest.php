<?php

namespace Tests\Unit\Modules\Authentication\Services;

use App\Models\User;
use App\Modules\Authentication\DTOs\LoginData;
use App\Modules\Authentication\DTOs\RegisterUserData;
use App\Modules\Authentication\Exceptions\EmailAlreadyExistsException;
use App\Modules\Authentication\Exceptions\InvalidCredentialsException;
use App\Modules\Authentication\Repositories\Interface\IUser;
use App\Modules\Authentication\Services\Implementations\AuthService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    private IUser $userRepository;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = Mockery::mock(IUser::class);

        $this->authService = new AuthService(
            $this->userRepository
        );
    }

    public function test_it_registers_a_new_user(): void
    {
        $data = new RegisterUserData(
            name: 'Gamal Sobhy',
            email: 'gamal@example.com',
            password: 'password123'
        );

        $storedUser = new User([
            'name' => $data->name,
            'email' => $data->email,
        ]);

        $storedUser->id = 1;

        $this->userRepository
            ->shouldReceive('getByEmail')
            ->once()
            ->with($data->email)
            ->andReturnNull();

        $this->userRepository
            ->shouldReceive('store')
            ->once()
            ->with($data)
            ->andReturn($storedUser);

        $result = $this->authService->register($data);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame($data->email, $result->email);
    }

    public function test_it_throws_exception_when_email_exists(): void
    {
        $data = new RegisterUserData(
            name: 'Gamal Sobhy',
            email: 'gamal@example.com',
            password: 'password123'
        );

        $existingUser = new User([
            'email' => $data->email,
        ]);

        $this->userRepository
            ->shouldReceive('getByEmail')
            ->once()
            ->with($data->email)
            ->andReturn($existingUser);

        $this->userRepository
            ->shouldNotReceive('store');

        $this->expectException(
            EmailAlreadyExistsException::class
        );

        $this->authService->register($data);
    }

    public function test_it_rejects_invalid_login_credentials(): void
    {
        $data = new LoginData(
            email: 'gamal@example.com',
            password: 'wrong-password'
        );

        $user = new User([
            'email' => $data->email,
            'password' => Hash::make('correct-password'),
        ]);

        $this->userRepository
            ->shouldReceive('getByEmail')
            ->once()
            ->with($data->email)
            ->andReturn($user);

        $this->expectException(
            InvalidCredentialsException::class
        );

        $this->authService->login($data);
    }
}