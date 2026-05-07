<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    public function register()
    {
    }

    public function render($request, Throwable $e): JsonResponse|Response
    {
        if ($request->is('api/*') || $request->expectsJson()) {

            if ($e instanceof \Laravel\Passport\Exceptions\MissingScopeException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Brak wymaganych uprawnień.',
                    'code' => 403
                ], 403);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Brak autoryzacji.',
                    'code' => 401
                ], 401);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Błąd walidacji.',
                    'errors' => $e->errors(),
                    'code' => 422
                ], 422);
            }

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'code' => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500
            ], 500);
        }

        return parent::render($request, $e);
    }
}
