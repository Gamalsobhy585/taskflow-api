<?php

namespace App\Modules\Authentication\DTOs;

final readonly class ChangePasswordData
{
    public function __construct(
        public string $email,
        public string $oldPassword,
        public string $newPassword,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            oldPassword: $data['old_password'],
            newPassword: $data['new_password'],
        );
    }
}