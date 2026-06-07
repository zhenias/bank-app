<?php

namespace App\Services\Account\Transaction;

use App\Enums\Transaction\TransactionStatus;
use App\Enums\Transaction\TransactionType;
use App\Exceptions\Financial\InsufficientFundsException;
use App\Exceptions\Financial\InvalidAccountException;
use App\Exceptions\Financial\InvalidAmountException;
use App\Jobs\ProcessFlikPayment;
use App\Models\Account\Account;
use App\Models\Account\Flik\FlikCode;
use App\Models\Account\Transaction\Transaction;
use App\Models\User;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class TransactionService extends Service
{
    public function transfer(
        User $user,
        string $toAccountNumber,
        int $amountInCents,
        ?string $description = null,
        ?string $reference = null,
    ): Transaction {
        return DB::transaction(function () use ($user, $toAccountNumber, $amountInCents, $description, $reference) {
            $toAccount = Account::where('account_number', $toAccountNumber)->first();

            if (! $toAccount) {
                throw new InvalidAccountException('Nie znaleziono konta docelowego.');
            }

            if ($toAccount->user_id === $user->id) {
                throw new InvalidAccountException('Nie możesz przelewać na własne konto.');
            }

            $fromAccount = $this->selectFromAccount($user);

            if ($amountInCents < 100) {
                throw new InvalidAmountException('Minimalna kwota to 0.01 PLN.');
            }

            $fromAccount = Account::lockForUpdate()->find($fromAccount->id);

            if ($fromAccount->balance < $amountInCents) {
                throw new InsufficientFundsException();
            }

            $fromAccount->balance -= $amountInCents;
            $fromAccount->save();

            $toAccount->balance += $amountInCents;
            $toAccount->save();

            return Transaction::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id'   => $toAccount->id,
                'amount'          => $amountInCents,
                'description'     => $description,
                'reference'       => $reference,
                'type'            => TransactionType::TRANSFER->value,
                'payment_method'  => 'transfer',
                'status'          => TransactionStatus::COMPLETED->value,
            ]);
        });
    }

    public function flikPayment(
        User $payer,
        User $receiver,
        int $amountInCents,
        ?string $cardId = null,
    ): Transaction {
        return DB::transaction(function () use ($payer, $receiver, $amountInCents, $cardId) {
            if ($payer->id === $receiver->id) {
                throw new InvalidAccountException('Nie możesz płacić samemu sobie.');
            }

            $fromAccount = $payer->accounts()
                ->where('currency', 'PLN')
                ->orderByDesc('balance')
                ->lockForUpdate()
                ->first();

            if (! $fromAccount) {
                throw new InvalidAccountException('Płatnik nie posiada konta w PLN.');
            }

            $toAccount = $receiver->accounts()
                ->where('currency', 'PLN')
                ->orderByDesc('balance')
                ->lockForUpdate()
                ->first();

            if (! $toAccount) {
                throw new InvalidAccountException('Odbiorca nie posiada konta w PLN.');
            }

            if ($fromAccount->balance < $amountInCents) {
                throw new InsufficientFundsException();
            }

            $fromAccount->balance -= $amountInCents;
            $fromAccount->save();

            $toAccount->balance += $amountInCents;
            $toAccount->save();

            return Transaction::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id'   => $toAccount->id,
                'from_card_id'    => $cardId,
                'amount'          => $amountInCents,
                'type'            => TransactionType::FLIK_PAYMENT->value,
                'payment_method'  => 'flik',
                'status'          => TransactionStatus::COMPLETED->value,
            ]);
        });
    }

    /**
     * Redeem a FLIK code: create PENDING transaction, reserve funds, dispatch job.
     */
    public function flikRedeem(FlikCode $flikCode, User $receiver): Transaction
    {
        return DB::transaction(function () use ($flikCode, $receiver) {
            // Re-load and lock flik code
            $code = FlikCode::find($flikCode->id);

            if (! $code) {
                throw new InvalidAccountException('Kod FLIK nie znaleziony.');
            }

            if ($code->isExpired() || 'active' !== $code->status) {
                throw new InvalidAccountException('Kod FLIK nieaktywny lub wygasł.');
            }

            // Payer is the owner of the card that generated the code
            $payer = $code->card->account->user;

            if ($payer->id === $receiver->id) {
                throw new InvalidAccountException('Nie możesz płacić samemu sobie.');
            }

            // Lock accounts
            $fromAccount = $payer->accounts()->where('currency', 'PLN')->orderByDesc('balance')->first();
            $toAccount   = $receiver->accounts()->where('currency', 'PLN')->orderByDesc('balance')->first();

            if (! $fromAccount) {
                throw new InvalidAccountException('Płatnik nie posiada konta w PLN.');
            }

            if (! $toAccount) {
                throw new InvalidAccountException('Odbiorca nie posiada konta w PLN.');
            }

            $amountInCents = $code->amount;

            if ($fromAccount->balance < $amountInCents) {
                throw new InsufficientFundsException();
            }

            $fromAccount->update([
                'balance' => $fromAccount->balance - $amountInCents,
            ]);

            // Create PENDING transaction
            $tx = Transaction::create([
                'from_account_id' => $fromAccount->id,
                'to_account_id'   => $toAccount->id,
                'from_card_id'    => $code->card_id,
                'amount'          => $amountInCents,
                'type'            => TransactionType::FLIK_PAYMENT->value,
                'payment_method'  => 'flik',
                'status'          => TransactionStatus::PENDING->value,
            ]);

            // Mark code as used immediately to prevent double-spend
            $code->update([
                'status'                 => 'used',
                'used_in_transaction_id' => $tx->id,
            ]);

            // Dispatch async job to complete the transaction
            ProcessFlikPayment::dispatch($tx->id);

            return $tx;
        });
    }

    private function selectFromAccount(User $user): Account
    {
        return $user->accounts()
            ->where('currency', 'PLN')
            ->orderByDesc('balance')
            ->first();
    }
}
