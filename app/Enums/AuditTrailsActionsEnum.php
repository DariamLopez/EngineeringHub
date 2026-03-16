<?php

namespace App\Enums;

enum AuditTrailsActionsEnum: string
{
    case CREATED = 'created';
    case UPDATED = 'updated';
    case STATUS_CHANGED = 'status_changed';
    case VALIDATED = 'validated';
    case COMPLETED = 'completed';
    case DELETED = 'deleted';
    public static function values(): array
    {
        return [
            self::CREATED->value,
            self::UPDATED->value,
            self::STATUS_CHANGED->value,
            self::VALIDATED->value,
            self::COMPLETED->value,
            self::DELETED->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
