<?php

namespace App\Exceptions\Financial;

use Illuminate\Http\JsonResponse;

class InvalidAccountException extends \Exception
{
    protected $message = 'Konto odbiorcy nie istnieje.';

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'code'    => 'INVALID_ACCOUNT',
        ], 422);
    }
}
