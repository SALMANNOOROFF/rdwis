<?php

namespace App\Enums;

enum RevType: int
{
    case FULL_CASCADE = 1;
    case FIELD_LEVEL = 2;
    case LINKED_CASCADE = 3;

    public function label(): string
    {
        return match ($this) {
            self::FULL_CASCADE => 'Full Cascade Reversal',
            self::FIELD_LEVEL => 'Field-Level Revision',
            self::LINKED_CASCADE => 'Linked Cascade Reversal',
        };
    }

    public function isCascade(): bool
    {
        return in_array($this, [self::FULL_CASCADE, self::LINKED_CASCADE], true);
    }
}
