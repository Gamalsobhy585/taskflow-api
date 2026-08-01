<?php

namespace App\Modules\Authentication\Services\Interface;

use App\Models\User;
use App\Modules\Authentication\DTOs\ChangePasswordData;
use App\Modules\Authentication\DTOs\LoginData;
use App\Modules\Authentication\DTOs\RegisterUserData;
use App\Modules\Authentication\Resources\UserResource;

interface IAuthService
{
    public function register(RegisterUserData $data): User;

    public function login(LoginData $data): array;

    public function logout(User $user): bool;

    public function updateOldPassword(
        ChangePasswordData $data
    ): bool;

    public function getUserInfo(User $user): UserResource;
}