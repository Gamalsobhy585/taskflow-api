<?php

namespace App\Modules\Authentication\Services\Implementations;

use App\Models\User;
use App\Modules\Authentication\DTOs\ChangePasswordData;
use App\Modules\Authentication\DTOs\LoginData;
use App\Modules\Authentication\DTOs\RegisterUserData;
use App\Modules\Authentication\Exceptions\EmailAlreadyExistsException;
use App\Modules\Authentication\Exceptions\InvalidCredentialsException;
use App\Modules\Authentication\Exceptions\UserNotFoundException;
use App\Modules\Authentication\Repositories\Interface\IUser;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\Interface\IAuthService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService implements IAuthService
{
    public function __construct(
        private readonly IUser $userRepository
    ) {
    }

    public function register(RegisterUserData $data): User
    {
        if ($this->userRepository->getByEmail($data->email)) {
            throw new EmailAlreadyExistsException();
        }

        return DB::transaction(
            fn (): User => $this->userRepository->store($data)
        );
    }

    public function login(LoginData $data): array
    {
        $user = $this->userRepository->getByEmail($data->email);

        if (
            !$user ||
            !Hash::check($data->password, $user->password)
        ) {
            throw new InvalidCredentialsException();
        }

        $token = $user
            ->createToken('default_token')
            ->plainTextToken;

        return [
            'token' => $token,
            'user_data' => new UserResource($user),
        ];
    }

    public function logout(User $user): bool
    {
        $accessToken = $user->currentAccessToken();

        if (!$accessToken) {
            return false;
        }

        $accessToken->delete();

        return true;
    }

    public function updateOldPassword(
        ChangePasswordData $data
    ): bool {
        $user = $this->userRepository->getByEmail($data->email);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!Hash::check($data->oldPassword, $user->password)) {
            throw new InvalidCredentialsException(
                __('messages.renew.failed')
            );
        }

        return $this->userRepository->updatePassword(
            $user,
            $data->newPassword
        );
    }

    public function getUserInfo(User $user): UserResource
    {
        $cacheKey = 'user:info:' . $user->id;

        return Cache::remember(
            $cacheKey,
            now()->addHour(),
            function () use ($user): UserResource {
                $userInfo = $this->userRepository
                    ->getUserInfo($user);

                return new UserResource($userInfo);
            }
        );
    }
}