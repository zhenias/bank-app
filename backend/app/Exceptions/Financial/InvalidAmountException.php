<?php

namespace App\Exceptions\Financial;

use Illuminate\Http\JsonResponse;

class InvalidAmountException extends \Exception
{
    public function __construct(string $message = 'Nieprawidłowa kwota.')
    {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'code'    => 'INVALID_AMOUNT',
        ], 422);
    }
}
