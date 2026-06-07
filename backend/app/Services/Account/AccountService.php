<?php

namespace App\Services\Account;

use App\Enums\Card\CardNetwork;
use App\Enums\Card\CardType;
use App\Models\Account\Account;
use App\Services\Account\Card\CardService;
use App\Services\Generator\AccountNumberGeneratorService;
use App\Services\Service;
use Illuminate\Support\Facades\DB;

class AccountService extends Service
{
    use AccountNumberGeneratorService;

    public function __construct(
        private readonly CardService $cardService,
    ) {
    }

    /**
     * Create a new bank account with optional debit card.
     */
    public function createAccount(
        string $userId,
        string $name,
        string $currency = 'PLN',
        string $type = 'current',
        bool $withCard = true,
        CardNetwork $cardNetwork = CardNetwork::VISA,
        CardType $cardType = CardType::DEBIT,
    ): Account {
        return DB::transaction(function () use ($userId, $name, $currency, $type, $withCard, $cardNetwork, $cardType) {
            $account = Account::create([
                'user_id'        => $userId,
                'name'           => $name,
                'account_number' => $this->generateAccountNumber(),
                'balance'        => 0,
                'currency'       => $currency,
                'type'           => $type,
            ]);

            if ($withCard) {
                $this->cardService->createCard($account->id, $cardNetwork, $cardType);
            }

            return $account->load('cards');
        });
    }

    /**
     * Update account name only.
     */
    public function updateName(Account $account, string $name): Account
    {
        $account->update(['name' => $name]);

        return $account->fresh();
    }

    /**
     * Close account (only if balance is zero).
     */
    public function closeAccount(Account $account): void
    {
        if ($account->balance > 0) {
            throw new \Exception('Cannot close account with non-zero balance.');
        }

        DB::transaction(function () use ($account) {
            $account->cards()->update([
                'status' => 'blocked',
            ]);
            $account->update([
                'status' => 'closed',
            ]);
        });
    }

    /**
     * Generate unique account number.
     */
    private function generateAccountNumber(): string
    {
        do {
            $number = $this->generate();
        } while (Account::where('account_number', $number)->exists());

        return $number;
    }
}
