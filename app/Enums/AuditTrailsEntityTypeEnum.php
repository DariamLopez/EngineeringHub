<?php

namespace App\Enums;

enum AuditTrailsEntityTypeEnum: string
{
    case PROJECT = 'project';
    case ARTIFACT = 'artifact';
    case MODULE = 'module';

    public static function values(): array
    {
        return [
            self::PROJECT->value,
            self::ARTIFACT->value,
            self::MODULE->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
