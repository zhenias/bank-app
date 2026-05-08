<?php

use App\Exceptions\Handler;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Exceptions\MissingScopeException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        ]);

        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->group('api', [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->append([
            \Illuminate\Http\Middleware\HandleCors::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
//            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
//    ->withSingletons([
//        \Illuminate\Contracts\Debug\ExceptionHandler::class => \App\Exceptions\Handler::class,
//    ])
//    ->withSingletons([
//        Illuminate\Contracts\Debug\ExceptionHandler::class => Illuminate\Foundation\Exceptions\Handler::class,
//    ])
    ->withExceptions(function (Exceptions $exceptions) {
//        $exceptions->renderable(function (ValidationException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (AuthenticationException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (AuthorizationException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (ModelNotFoundException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (NotFoundHttpException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (UnauthorizedHttpException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (AccessDeniedHttpException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (ThrottleRequestsException $e) {
//            return Handler::handleException($e);
//        });
//
//        $exceptions->renderable(function (MissingScopeException $e) {
//            return Handler::handleException($e);
//        });
    })
    ->create();
