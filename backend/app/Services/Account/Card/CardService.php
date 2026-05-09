<?php

namespace App\Services\Account\Card;

use App\Enums\Card\CardNetwork;
use App\Enums\Card\CardType;
use App\Models\Account\Card\Card;
use App\Services\Generator\CardNumberGeneratorService;
use App\Services\Service;

class CardService extends Service
{
    use CardNumberGeneratorService;

    /**
     * Create a new card for an account.
     */
    public function createCard(
        string $accountId,
        CardNetwork $network = CardNetwork::VISA,
        CardType $type = CardType::DEBIT,
    ): Card {
        $expiry = $this->generateExpiryDate();

        do {
            $cardNumber = $this->generateCardNumber($network);
        } while (Card::where('card_number', $cardNumber)->exists());

        return Card::create([
            'account_id'  => $accountId,
            'card_number' => $cardNumber,
            'exp_month'   => $expiry['month'],
            'exp_year'    => $expiry['year'],
            'cvv'         => $this->generateCvv($network),
            'network'     => $network->value,
            'status'      => 'active',
            'type'        => $type->value,
        ]);
    }

    /**
     * Block a card.
     */
    public function blockCard(Card $card): Card
    {
        $card->update(['status' => 'blocked']);

        return $card->fresh();
    }

    /**
     * Unblock a card.
     */
    public function unblockCard(Card $card): Card
    {
        $card->update(['status' => 'active']);

        return $card->fresh();
    }
}
