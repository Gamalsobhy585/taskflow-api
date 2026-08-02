<?php

namespace App\Enums;

enum ProjectStatus: int
{
    case ACTIVE = 1;
    case COMPLETED = 2;
    case ARCHIVED = 3;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::COMPLETED => 'Completed',
            self::ARCHIVED => 'Archived',
        };
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $status): int => $status->value,
            self::cases()
        );
    }

    public static function options(): array
    {
        return array_map(
            static fn (self $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ],
            self::cases()
        );
    }
}