<?php

namespace App\Enums;

enum TaskStatus: int
{
    case TODO = 1;
    case IN_PROGRESS = 2;
    case DONE = 3;

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'Todo',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
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