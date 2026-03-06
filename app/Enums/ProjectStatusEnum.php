<?php

namespace App\Enums;

enum ProjectStatusEnum: string
{
    case DRAFT = 'draft';
    case DISCOVERY = 'discovery';
    case EXECUTION = 'execution';
    case DELIVERED = 'delivered';

    public static function values(): array
    {
        return [
            self::DRAFT->value,
            self::DISCOVERY->value,
            self::EXECUTION->value,
            self::DELIVERED->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
