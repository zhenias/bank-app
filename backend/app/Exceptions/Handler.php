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
use Symfony\Component\HttpKernel\Exception\HttpException;
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
        if ($e instanceof MissingScopeException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Insufficient permissions to access this resource.',
                'code'    => 403,
            ], 403);
        }

        if ($e instanceof HttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'code'    => 403,
            ], 403);
        }

        if ($e instanceof AuthenticationException || $e instanceof UnauthorizedHttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthenticated. Please provide valid credentials.',
                'code'    => 401,
            ], 401);
        }

        if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No permissions for this resource.',
                'code'    => 403,
            ], 403);
        }

        if ($e instanceof ValidationException) {
            $errors = $e->errors();
            $count = collect($errors)->flatten()->count();
            $firstMessage = collect($errors)->flatten()->first();

            $message = $count > 1
                ? $firstMessage . ' (and ' . ($count - 1) . ' more error' . ($count > 2 ? 's' : '') . ')'
                : $firstMessage;

            return response()->json([
                'status'  => 'error',
                'message' => $message,
                'errors'  => $errors,
                'code'    => 422,
            ], 422);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Resource not found.',
                'code'    => 404,
            ], 404);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Endpoint not found.',
                'code'    => 404,
            ], 404);
        }

        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'How many requests you have made. Please try again later.',
                'code'    => 429,
            ], 429);
        }

        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

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
            'message' => $e->getMessage() ?: 'Internal Server Error',
            'code'    => $statusCode,
        ], $statusCode >= 100 && $statusCode < 600 ? $statusCode : 500);
    }
}
