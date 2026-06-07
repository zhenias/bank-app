<?php

namespace App\Services\Account\Flik;

use App\Enums\Flik\FlikCodeStatus;
use App\Exceptions\Financial\FlikCodeExpiredException;
use App\Exceptions\Financial\FlikCodeInvalidException;
use App\Models\Account\Card\Card;
use App\Models\Account\Flik\FlikCode;
use App\Models\Account\Transaction\Transaction;
use App\Services\Service;

class FlikCodeService extends Service
{
    public function generate(Card $card, int $amountInCents): FlikCode
    {
        if ($card->account->balance < $amountInCents) {
            throw new \InvalidArgumentException('Niewystarczające środki na koncie.');
        }

        if ('PLN' !== $card->account->currency) {
            throw new \InvalidArgumentException('FLIK kod może być generowany tylko dla kart w PLN.');
        }

        if ('active' !== $card->status) {
            throw new \InvalidArgumentException('FLIK kod może być generowany tylko dla aktywnych kart.');
        }

        do {
            $code = str_pad((string) rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (FlikCode::where('code', $code)->exists());

        return FlikCode::create([
            'card_id'    => $card->id,
            'code'       => $code,
            'amount'     => $amountInCents,
            'expires_at' => now()->addMinutes(2),
            'status'     => FlikCodeStatus::ACTIVE->value,
        ]);
    }

    public function validate(string $code): FlikCode
    {
        $flikCode = FlikCode::where('code', $code)->first();

        if (! $flikCode) {
            throw new FlikCodeInvalidException();
        }

        if ($flikCode->isExpired()) {
            $flikCode->update(['status' => FlikCodeStatus::EXPIRED->value]);

            throw new FlikCodeExpiredException();
        }

        if ($flikCode->status !== FlikCodeStatus::ACTIVE->value) {
            throw new FlikCodeInvalidException();
        }

        return $flikCode;
    }

    public function validateCode(string $code): FlikCode
    {
        $flikCode = FlikCode::where('code', $code)->first();

        if (! $flikCode) {
            throw new FlikCodeInvalidException();
        }

        return $flikCode;
    }

    public function markAsUsed(FlikCode $flikCode, Transaction $transaction): void
    {
        $flikCode->update([
            'status'                 => FlikCodeStatus::USED->value,
            'used_in_transaction_id' => $transaction->id,
        ]);
    }

    public function getStatus(FlikCode $flikCode): FlikCodeStatus
    {
        if ($flikCode->isExpired()) {
            return FlikCodeStatus::EXPIRED;
        }

        return FlikCodeStatus::from($flikCode->status);
    }
}
