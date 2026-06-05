<?php

namespace App\Exceptions\Financial;

use Exception;
use Illuminate\Http\JsonResponse;

class FlikCodeExpiredException extends Exception
{
    protected $message = 'Kod FLIK wygasł.';

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'code' => 'FLIK_CODE_EXPIRED',
        ], 422);
    }
}

