<?php

namespace App\Modules\Authentication\Repositories\Interface;

use App\Models\User;
use App\Modules\Authentication\DTOs\RegisterUserData;

interface IUser
{
    public function getByEmail(string $email): ?User;

    public function store(RegisterUserData $userData): User;

    public function updatePassword(
        User $user,
        string $newPassword
    ): bool;

    public function getUserInfo(User $user): User;
}