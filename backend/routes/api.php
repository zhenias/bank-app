<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\Card\CardController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::middleware(['auth:api'])->group(function () {
    Route::prefix('/user')->group(function () {
        Route::middleware('guest')->withoutMiddleware(['auth:api'])->group(function () {
            Route::post('/register', [ProfileController::class, 'create']);
        });

        Route::prefix('/profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::patch('/', [ProfileController::class, 'update']);
            Route::delete('/', [ProfileController::class, 'destroy']);

            Route::post('/revoke-token', [ProfileController::class, 'revokeToken']);
        });

        Route::middleware([CheckToken::using('user-balance')])->get('/balance', function (Request $request) {
            return [
                'balance'  => 0.00,
                'currency' => 'PLN',
            ];
        });
    });

    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('accounts.cards', CardController::class)->only(['index', 'store']);
    Route::get('cards/{card}', [CardController::class, 'show']);
    Route::patch('cards/{card}/block', [CardController::class, 'block']);
    Route::patch('cards/{card}/unblock', [CardController::class, 'unblock']);
});
