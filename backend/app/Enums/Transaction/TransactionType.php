<?php

namespace App\Enums\Transaction;

enum TransactionType: string
{
    case TRANSFER       = 'transfer';
    case FLIK_PAYMENT   = 'flik_payment';
    case ATM_WITHDRAWAL = 'atm_withdrawal';
    case DEPOSIT        = 'deposit';
    case CARD_PAYMENT   = 'card_payment';

    public function label(): string
    {
        return match ($this) {
            self::TRANSFER       => 'Przelew',
            self::FLIK_PAYMENT   => 'Płatność FLIK',
            self::ATM_WITHDRAWAL => 'Wypłata z bankomatu',
            self::DEPOSIT        => 'Wpłata',
            self::CARD_PAYMENT   => 'Płatność kartą',
        };
    }
}
