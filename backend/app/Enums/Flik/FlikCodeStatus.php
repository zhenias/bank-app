<?php

namespace App\Enums\Flik;

enum FlikCodeStatus: string
{
    case ACTIVE  = 'active';
    case USED    = 'used';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE  => 'Aktywny',
            self::USED    => 'Użyty',
            self::EXPIRED => 'Wygasł',
        };
    }
}
