<?php

namespace App\Enums\Card;

enum CardType: string
{
    case DEBIT   = 'debit';
    case CREDIT  = 'credit';
    case PREPAID = 'prepaid';
    case VIRTUAL = 'virtual';

    public function label(): string
    {
        return match ($this) {
            self::DEBIT   => 'Karta debetowa',
            self::CREDIT  => 'Karta kredytowa',
            self::PREPAID => 'Karta przedpłacona',
            self::VIRTUAL => 'Karta wirtualna',
        };
    }
}
