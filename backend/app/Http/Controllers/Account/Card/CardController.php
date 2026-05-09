<?php

namespace App\Http\Controllers\Account\Card;

use App\Http\Controllers\Controller;
use App\Http\Resources\Account\Card\CardResource;
use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Services\Account\Card\CardService;
use Illuminate\Http\JsonResponse;

/**
 * Zarządzanie kartami płatniczymi.
 *
 * @tags Card
 */
class CardController extends Controller
{
    public function __construct(
        private readonly CardService $cardService,
    ) {
    }

    /**
     * Wyświetla listę kart płatniczych przypisanych do konta.
     */
    public function index(Account $account)
    {
        return CardResource::collection(
            $account->cards()->paginate(20),
        );
    }

    /**
     * Tworzy nową kartę płatniczą dla konta.
     */
    public function store(Account $account): JsonResponse
    {
        $card = $this->cardService->createCard($account->id);

        return new CardResource($card)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Wyświetla szczegóły karty płatniczej.
     */
    public function show(Card $card): CardResource
    {
        return new CardResource($card);
    }

    /**
     * Blokuje kartę płatniczą.
     */
    public function block(Card $card): CardResource
    {
        $card = $this->cardService->blockCard($card);

        return new CardResource($card);
    }

    /**
     * Odblokowuje kartę płatniczą.
     */
    public function unblock(Card $card): CardResource
    {
        $card = $this->cardService->unblockCard($card);

        return new CardResource($card);
    }
}
