<?php

namespace App\Http\Controllers\Account\Transaction;

use App\Http\Concerns\WithPagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Resources\Account\Transaction\TransactionResource;
use App\Models\Account\Account;
use App\Models\Account\Card\Card;
use App\Models\Account\Transaction\Transaction;
use App\Services\Account\Transaction\TransactionService;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Passport\Attributes\AuthorizeToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Transakcje.
 *
 * @tags Transakcje
 */
class TransactionController extends Controller
{
    use WithPagination;

    public function __construct(
        private readonly TransactionService $transactionService,
    ) {
    }

    /**
     * Lista transakcji użytkownika.
     *
     * Lista transakcji użytkownika, zarówno wysłanych, jak i otrzymanych.
     */
    #[AuthorizeToken(['transactions-view'], anyScope: true)]
    #[QueryParameter('per_page', description: 'Ilość na stronę', type: 'int', default: 20)]
    #[QueryParameter('page', description: 'Numer strony', type: 'int', default: 1)]
    public function index(): AnonymousResourceCollection
    {
        $transactions = Transaction::whereHas('fromAccount', function ($q) {
            $q->where('user_id', auth()->id());
        })->orWhereHas('toAccount', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->orderBy('created_at', 'desc')
        ->paginate($this->perPage());

        return TransactionResource::collection($transactions);
    }

    /**
     * Tworzenie nowej transakcji.
     *
     * Tworzenie nowej transakcji - przelew między kontami. Użytkownik musi być właścicielem konta źródłowego.
     */
    #[AuthorizeToken(['transactions-create'], anyScope: true)]
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->transfer(
            user: auth()->user(),
            toAccountNumber: $request->validated('to_account_number'),
            amountInCents: (int) ($request->validated('amount') * 100),
            description: $request->validated('description'),
            reference: $request->validated('reference'),
        );

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Szczegóły transakcji.
     *
     * Pobranie szczegółów konkretnej transakcji. Użytkownik musi być właścicielem konta źródłowego lub docelowego.
     */
    #[AuthorizeToken(['transactions-view'], anyScope: true)]
    #[PathParameter('transaction', description: 'ID transakcji', type: 'string', format: 'uuid')]
    public function show(Transaction $transaction): TransactionResource
    {
        if ($transaction->fromAccount?->user_id  !== auth()->id()
            && $transaction->toAccount?->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }

        return new TransactionResource($transaction);
    }

    /**
     * Transakcje dla konta.
     *
     * Pobranie listy transakcji związanych z konkretnym kontem. Użytkownik musi być właścicielem tego konta.
     */
    #[AuthorizeToken(['transactions-view'], anyScope: true)]
    #[PathParameter('account', description: 'ID konta', type: 'string', format: 'uuid')]
    #[QueryParameter('per_page', description: 'Ilość na stronę', type: 'int', default: 20)]
    public function accountTransactions(Account $account): AnonymousResourceCollection
    {
        if ($account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }

        $transactions = Transaction::where(function ($q) use ($account) {
            $q->where('from_account_id', $account->id)
                ->orWhere('to_account_id', $account->id);
        })
        ->orderBy('created_at', 'desc')
        ->paginate($this->perPage());

        return TransactionResource::collection($transactions);
    }

    /**
     * Transakcje dla karty.
     *
     * Pobranie listy transakcji związanych z konkretną kartą. Użytkownik musi być właścicielem konta, do którego przypisana jest karta.
     */
    #[AuthorizeToken(['transactions-view'], anyScope: true)]
    #[PathParameter('card', description: 'ID karty', type: 'string', format: 'uuid')]
    #[QueryParameter('per_page', description: 'Ilość na stronę', type: 'int', default: 20)]
    public function cardTransactions(Card $card): AnonymousResourceCollection
    {
        if ($card->account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }

        $transactions = Transaction::where('from_card_id', $card->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage());

        return TransactionResource::collection($transactions);
    }
}
