<?php

namespace App\Enums;

enum ArtifactTypeEnum: string
{
    case STRATEGIC_ALIGNMENT = 'strategic_alignment';
    case BIG_PICTURE = 'big_picture';
    case DOMAIN_BREAKDOWN = 'domain_breakdown';
    case MODULE_MATRIX = 'module_matrix';
    case MODULE_ENGINEERING = 'module_engineering';
    case SYSTEM_ARCHITECTURE = 'system_architecture';
    case PHASE_SCOPE = 'phase_scope';

    public static function values(): array
    {
        return [
            self::STRATEGIC_ALIGNMENT->value,
            self::BIG_PICTURE->value,
            self::DOMAIN_BREAKDOWN->value,
            self::MODULE_MATRIX->value,
            self::MODULE_ENGINEERING->value,
            self::SYSTEM_ARCHITECTURE->value,
            self::PHASE_SCOPE->value,
        ];
    }
    public function value(): string
    {
        return $this->value;
    }
}
