<?php

namespace App\Http\Controllers\Account\Flik;

use App\Http\Controllers\Controller;
use App\Http\Requests\Flik\RequestFlikCodeRequest;
use App\Http\Requests\Flik\StoreFlikPaymentRequest;
use App\Http\Resources\Account\Flik\FlikCodeResource;
use App\Http\Resources\Account\Transaction\TransactionResource;
use App\Models\Account\Card\Card;
use App\Services\Account\Flik\FlikCodeService;
use App\Services\Account\Transaction\TransactionService;
use Dedoc\Scramble\Attributes\PathParameter;
use Illuminate\Http\JsonResponse;
use Laravel\Passport\Attributes\AuthorizeToken;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * FLIK
 *
 * FLIK - szybki sposób na płatności między użytkownikami. Umożliwia generowanie jednorazowych kodów płatności, które można zrealizować w ciągu 2 minut. Idealny do szybkich transakcji, np. podczas spotkań, bez konieczności podawania danych konta. Użytkownik generuje kod dla określonej kwoty, a odbiorca może go zrealizować, płacąc bezpośrednio z karty powiązanej z kontem. Po zrealizowaniu kodu środki są natychmiast przekazywane na konto odbiorcy.
 *
 * @tags FLIK
 */
class FlikCodeController extends Controller
{
    public function __construct(
        private readonly FlikCodeService $flikCodeService,
        private readonly TransactionService $transactionService,
    ) {
    }

    /**
     * Generowanie kodu FLIK
     *
     * Generuje jednorazowy kod FLIK dla określonej kwoty, powiązany z kartą użytkownika. Kod jest ważny przez 2 minuty i może być zrealizowany przez innego użytkownika, który zna ten kod. Użytkownik musi być właścicielem karty, dla której generuje kod.
     */
    #[AuthorizeToken(['flik-generate'], anyScope: true)]
    #[PathParameter('card', description: 'ID karty', type: 'string', format: 'uuid')]
    public function requestCode(RequestFlikCodeRequest $request, Card $card): JsonResponse
    {
        if ($card->account->user_id !== auth()->id()) {
            throw new AccessDeniedHttpException();
        }

        $amountInCents = (int) ($request->validated('amount') * 100);
        $flikCode = $this->flikCodeService->generate($card, $amountInCents);

        return response()->json([
            'code' => $flikCode->code,
            // amount in cents
            'amount' => $flikCode->amount,
            'expires_in_seconds' => 120,
            'message' => 'Kod FLIK wygenerowany. Ważny przez 2 minuty.',
        ], 201);
    }

    /**
     * Realizacja płatności FLIK
     *
     * Realizuje płatność za pomocą kodu FLIK. Użytkownik podaje kod, a system weryfikuje jego ważność i realizuje transakcję między właścicielem karty, która wygenerowała kod (płatnik), a aktualnie zalogowanym użytkownikiem (odbiorca). Po zrealizowaniu kod jest oznaczany jako użyty, a środki są przekazywane na konto odbiorcy.
     */
    #[AuthorizeToken(['flik-pay'], anyScope: true)]
    public function pay(StoreFlikPaymentRequest $request): JsonResponse
    {
        $flikCode = $this->flikCodeService->validate($request->validated('code'));

        // Payer is the owner of the card that generated the code
        $payer = $flikCode->card->account->user;
        $receiver = auth()->user();

        $transaction = $this->transactionService->flikRedeem($flikCode, $receiver);

        return response()->json([
            'transaction' => new TransactionResource($transaction),
            'message' => 'Płatność FLIK zrealizowana. Na twoje konto wpłynęła kwota ' . number_format($transaction->amount / 100, 2) . ' PLN.',
        ], 201);
    }
}
