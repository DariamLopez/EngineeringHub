<?php

namespace App\Enums;

enum AuditTrailsEntityTypeEnum: string
{
    case PROJECT = 'project';
    case ARTIFACT = 'artifact';
    case DOMAIN = 'domain';

    public static function values(): array
    {
        return [
            self::PROJECT->value,
            self::ARTIFACT->value,
            self::DOMAIN->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
