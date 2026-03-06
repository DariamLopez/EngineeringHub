<?php

namespace App\Enums;

enum ModuleStatusEnum: string
{
    case DRAFT = 'draft';
    case VALIDATED = 'validated';
    case READY_FOR_BUILD = 'ready_for_build';

    public static function values(): array
    {
        return [
            self::DRAFT->value,
            self::VALIDATED->value,
            self::READY_FOR_BUILD->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
