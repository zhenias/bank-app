<?php

namespace App\Enums\Card;

enum CardNetwork: string
{
    case VISA       = 'visa';
    case MASTERCARD = 'mastercard';

    public function prefix(): array
    {
        return match ($this) {
            self::VISA       => ['4'],
            self::MASTERCARD => ['51', '52', '53', '54', '55'],
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::VISA       => 'Visa',
            self::MASTERCARD => 'Mastercard',
        };
    }

    public function cvvLength(): int
    {
        return 3;
    }
}
