<?php

namespace App\Http\Controllers\Account\Card;

use App\Http\Concerns\WithPagination;
use App\Http\Controllers\Controller;
use App\Http\Resources\Account\Card\CardResource;
use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Services\Account\Card\CardService;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Zarządzanie kartami płatniczymi.
 *
 * @tags Card
 *
 * @queryParam account integer ID konta, do którego przypisana jest karta. Required. Example: "123e4567-e89b-12d3-a456-426614174000"
 */
class CardController extends Controller
{
    use WithPagination;

    public function __construct(
        private readonly CardService $cardService,
    ) {
    }

    /**
     * Wyświetla listę kart płatniczych przypisanych do konta.
     */
    #[QueryParameter('per_page', description: 'Ilość elementów na stronę.', type: 'int', default: 20, example: 30)]
    #[QueryParameter('page', description: 'Numer obecnej strony.', type: 'int', default: 1, example: 2)]
    #[PathParameter('account', description: 'ID konta', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function index(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException('Account not found or access denied.');
        }

        $cards = $account->cards();

        return CardResource::collection(
            $this->paginate($cards)
        );
    }

    /**
     * Tworzy nową kartę płatniczą dla konta.
     */
    #[PathParameter('account', description: 'ID konta', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function store(Account $account): JsonResponse
    {
        if ($account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException('Account not found or access denied.');
        }

        $card = $this->cardService->createCard($account->id);

        return new CardResource($card)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Wyświetla szczegóły karty płatniczej.
     */
    #[PathParameter('card', description: 'ID karty', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function show(Card $card): CardResource
    {
        if ($card->account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException('Card not found or access denied.');
        }

        return new CardResource($card);
    }

    /**
     * Blokuje kartę płatniczą.
     */
    #[PathParameter('card', description: 'ID karty', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function block(Card $card): CardResource
    {
        if ($card->account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException('Card not found or access denied.');
        }

        $card = $this->cardService->blockCard($card);

        return new CardResource($card);
    }

    /**
     * Odblokowuje kartę płatniczą.
     */
    #[PathParameter('card', description: 'ID karty', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function unblock(Card $card): CardResource
    {
        if ($card->account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException('Card not found or access denied.');
        }

        $card = $this->cardService->unblockCard($card);

        return new CardResource($card);
    }
}
