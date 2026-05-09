<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Handler extends ExceptionHandler
{
    public function register(): void
    {
        $this->reportable(function (\Throwable $e) {
        });
    }

    public function render($request, \Throwable $e): Response
    {
        if ($request->is('api*')) {
            return $this->handleApiException($e);
        }

        return parent::render($request, $e);
    }

    private function handleApiException(\Throwable $e): JsonResponse
    {
        // MissingScopeException (Passport)
        if ($e instanceof MissingScopeException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Brak wymaganych uprawnień.',
                'code'    => 403,
            ], 403);
        }

        // AuthenticationException (brak autoryzacji)
        if ($e instanceof AuthenticationException || $e instanceof UnauthorizedHttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Brak autoryzacji.',
                'code'    => 401,
            ], 401);
        }

        // AuthorizationException (brak dostępu)
        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Brak uprawnień do tego zasobu.',
                'code'    => 403,
            ], 403);
        }

        // ValidationException (błędy walidacji)
        if ($e instanceof ValidationException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Błąd walidacji danych.',
                'errors'  => $e->errors(),
                'code'    => 422,
            ], 422);
        }

        // ModelNotFoundException (model nie znaleziony)
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Żądany zasób nie został znaleziony.',
                'code'    => 404,
            ], 404);
        }

        // NotFoundHttpException (endpoint nie istnieje)
        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Endpoint nie istnieje.',
                'code'    => 404,
            ], 404);
        }

        // ThrottleRequestsException (zbyt wiele zapytań)
        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Zbyt wiele żądań. Spróbuj ponownie później.',
                'code'    => 429,
            ], 429);
        }

        // Domyślny błąd
        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

        // W trybie debug pokaż więcej informacji
        if (config('app.debug')) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'code'    => $statusCode,
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => collect($e->getTrace())->take(5)->toArray(),
            ], $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500);
        }

        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage() ?: 'Wystąpił wewnętrzny błąd serwera.',
            'code'    => $statusCode,
        ], $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500);
    }
}
