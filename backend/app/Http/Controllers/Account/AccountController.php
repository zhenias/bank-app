<?php

namespace App\Http\Controllers\Account;

use App\Http\Concerns\WithPagination;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Resources\Account\AccountResource;
use App\Http\Resources\Account\BalanceResource;
use App\Models\Account\Account;
use App\Services\Account\AccountService;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\PathParameter;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Laravel\Passport\Attributes\AuthorizeToken;

/**
 * Zarządzanie kontem bankowym.
 *
 * @tags Konto bankowe
 */
class AccountController extends Controller
{
    use WithPagination;

    public function __construct(
        private readonly AccountService $accountService,
    ) {
    }

    /**
     * Wyświetla listę kont bankowych użytkownika.
     */
    #[AuthorizeToken(['accounts-view'], anyScope: true)]
    #[QueryParameter('per_page', description: 'Ilość elementów na stronę.', type: 'int', default: 20, example: 30)]
    #[QueryParameter('page', description: 'Numer obecnej strony.', type: 'int', default: 1, example: 2)]
    public function index(): AnonymousResourceCollection
    {
        $accounts = Account::where('user_id', auth()->id())
            ->with('cards')
            ->paginate(
                perPage: $this->perPage(),
                page: $this->currentPage(),
            );

        return AccountResource::collection($accounts);
    }

    /**
     * Saldo.
     *
     * Wyświetlanie salda według kont w którym jest waluta.
     */
    #[AuthorizeToken(['accounts-view'], anyScope: true)]
    public function balance(): JsonResponse
    {
        $balances = auth()->user()->accounts()
            ->select('currency')
            ->selectRaw('SUM(balance) as total_balance')
            ->groupBy('currency')
            ->get();

        return response()->json([
            'balances' => BalanceResource::collection($balances),
            /* @example 2500.00 */
            'total' => number_format($balances->where('currency', 'PLN')->sum('total_balance') / 100, 2, '.', ''),
        ]);
    }

    /**
     * Tworzy nowe konto bankowe dla użytkownika.
     */
    #[AuthorizeToken(['accounts-manage'], anyScope: true)]
    #[BodyParameter('name', description: 'Nazwa konta', type: 'string', example: 'Konto oszczędnościowe')]
    #[BodyParameter('currency', description: 'Waluta konta', type: 'string', example: 'PLN')]
    #[BodyParameter('type', description: 'Typ konta', type: 'string', example: 'current')]
    #[BodyParameter('with_card', description: 'Czy utworzyć kartę płatniczą', type: 'boolean', example: true)]
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
    #[AuthorizeToken(['accounts-details'], anyScope: true)]
    #[PathParameter('account', description: 'Account being viewed', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function show(Account $account): AccountResource
    {
        return new AccountResource($account->load('cards'));
    }

    /**
     * Aktualizuje nazwę konta bankowego.
     */
    #[AuthorizeToken(['accounts-manage'], anyScope: true)]
    #[PathParameter('account', description: 'ID konta bankowego', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    #[BodyParameter('name', description: 'Nowa nazwa konta', type: 'string', example: 'Konto oszczędnościowe')]
    public function update(UpdateAccountRequest $request, Account $account): AccountResource
    {
        $account = $this->accountService->updateName($account, $request->name);

        return new AccountResource($account->load('cards'));
    }

    /**
     * Usuwa konto bankowe, zamykając je.
     */
    #[AuthorizeToken(['accounts-manage'], anyScope: true)]
    #[PathParameter('account', description: 'ID konta bankowego', type: 'string', format: 'uuid', example: '550e8400-e29b-41d4-a716-446655440000')]
    public function destroy(Account $account): JsonResponse
    {
        $this->accountService->closeAccount($account);

        return response()->json(['message' => 'Account closed.'], 200);
    }
}
