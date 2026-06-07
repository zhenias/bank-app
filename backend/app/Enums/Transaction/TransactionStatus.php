<?php

namespace App\Enums\Transaction;

enum TransactionStatus: string
{
    case PENDING   = 'pending';
    case COMPLETED = 'completed';
    case FAILED    = 'failed';
    case REVERSED  = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING   => 'Oczekująca',
            self::COMPLETED => 'Zrealizowana',
            self::FAILED    => 'Nieudana',
            self::REVERSED  => 'Anulowana',
        };
    }
}
