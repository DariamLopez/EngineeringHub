<?php

namespace App\Enums;

enum ArtifactStatusEnum: string
{
    case NOT_STARTED = 'not_started';
    case IN_PROGRESS = 'in_progress';
    case BLOCKED = 'blocked';
    case DONE = 'done';

    public static function values(): array
    {
        return [
            self::NOT_STARTED->value,
            self::IN_PROGRESS->value,
            self::BLOCKED->value,
            self::DONE->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
