<?php

namespace App\Exceptions\Financial;

use Illuminate\Http\JsonResponse;

class InsufficientFundsException extends \Exception
{
    protected $message = 'Niewystarczające środki na koncie.';

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'code'    => 'INSUFFICIENT_FUNDS',
        ], 422);
    }
}
