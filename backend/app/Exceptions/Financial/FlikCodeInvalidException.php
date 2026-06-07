<?php

namespace App\Exceptions\Financial;

use Illuminate\Http\JsonResponse;

class FlikCodeInvalidException extends \Exception
{
    protected $message = 'Kod FLIK jest nieprawidłowy lub już został użyty.';

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'code'    => 'FLIK_CODE_INVALID',
        ], 422);
    }
}
