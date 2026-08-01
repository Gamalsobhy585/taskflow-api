<?php

namespace App\Modules\Authentication\Repositories\Implementation;

use App\Models\User;
use App\Modules\Authentication\DTOs\RegisterUserData;
use App\Modules\Authentication\Repositories\Interface\IUser;

class UserRepository implements IUser
{
    public function getByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    public function store(RegisterUserData $userData): User
    {
        return User::query()->create(
            $userData->toArray()
        );
    }

    public function updatePassword(
        User $user,
        string $newPassword
    ): bool {
        return $user->update([
            'password' => $newPassword,
        ]);
    }

    public function getUserInfo(User $user): User
    {
        return $user;
    }
}