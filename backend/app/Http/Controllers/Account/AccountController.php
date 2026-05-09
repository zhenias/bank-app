<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Resources\Account\AccountResource;
use App\Models\Account\Account;
use App\Services\Account\AccountService;
use Illuminate\Http\JsonResponse;

/**
 * Zarządzanie kontem bankowym.
 *
 * @tags Account
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService,
    ) {
    }

    /**
     * Wyświetla listę kont bankowych użytkownika.
     */
    public function index()
    {
        return AccountResource::collection(
            Account::where('user_id', auth()->id())
                ->with('cards')
                ->paginate(20),
        );
    }

    /**
     * Tworzy nowe konto bankowe dla użytkownika.
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = $this->accountService->createAccount(
            userId: auth()->id(),
            name: $request->name,
            currency: $request->currency  ?? 'PLN',
            type: $request->type          ?? 'current',
            withCard: $request->with_card ?? true,
        );

        return new AccountResource($account->load('cards'))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Wyświetla szczegóły konta bankowego, w tym powiązane karty.
     */
    public function show(Account $account): AccountResource
    {
        return new AccountResource($account->load('cards'));
    }

    /**
     * Aktualizuje nazwę konta bankowego.
     */
    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $account = $this->accountService->updateName($account, $request->name);

        return new AccountResource($account->load('cards'));
    }

    /**
     * Usuwa konto bankowe, zamykając je.
     */
    public function destroy(Account $account): JsonResponse
    {
        $this->accountService->closeAccount($account);

        return response()->json(['message' => 'Account closed.'], 200);
    }
}
