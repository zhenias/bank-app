<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\Card\CardController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::prefix('/')->group(function () {
    return response()->json([
        'message' => 'Access denied.',
    ]);
});

Route::middleware(['auth:api'])->group(function () {
    Route::post('/guardian/approve/{wardId}', [ProfileController::class, 'approveGuardian']);
    Route::post('/guardian/reject/{wardId}', [ProfileController::class, 'rejectGuardian']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::prefix('/user')->group(function () {
        Route::middleware('guest')->withoutMiddleware(['auth:api', 'verified'])->group(function () {
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

    Route::middleware(['adult'])->group(function () {
        Route::get('accounts/balance', [AccountController::class, 'balance']);
        Route::apiResource('accounts', AccountController::class);
        Route::apiResource('accounts.cards', CardController::class)->only(['index', 'store']);
        Route::get('cards/{card}', [CardController::class, 'show']);
        Route::patch('cards/{card}/block', [CardController::class, 'block']);
        Route::patch('cards/{card}/unblock', [CardController::class, 'unblock']);
        Route::delete('cards/{card}', [CardController::class, 'destroy']);
    });
});
