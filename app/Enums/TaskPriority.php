<?php

namespace App\Enums;

enum TaskPriority: int
{
    case LOW = 1;
    case MEDIUM = 2;
    case HIGH = 3;

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Low',
            self::MEDIUM => 'Medium',
            self::HIGH => 'High',
        };
    }

    public static function values(): array
    {
        return array_map(
            static fn (self $priority): int => $priority->value,
            self::cases()
        );
    }

    public static function options(): array
    {
        return array_map(
            static fn (self $priority): array => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ],
            self::cases()
        );
    }
}