<?php

use App\Http\Controllers\Account\AccountController;
use App\Http\Controllers\Account\Card\CardController;
use App\Http\Controllers\Account\Flik\FlikCodeController;
use App\Http\Controllers\Account\Transaction\TransactionController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\User\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('/')->group(function () {
    return response()->json([
        'message' => 'Access denied.',
    ]);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    Route::prefix('/user')->group(function () {
        Route::post('/guardian/approve/{wardId}', [ProfileController::class, 'approveGuardian']);
        Route::post('/guardian/reject/{wardId}', [ProfileController::class, 'rejectGuardian']);

        Route::middleware('guest')->withoutMiddleware(['auth:api', 'verified'])->group(function () {
            Route::post('/register', [ProfileController::class, 'create']);
        });

        Route::prefix('/profile')->group(function () {
            Route::get('/', [ProfileController::class, 'show']);
            Route::patch('/', [ProfileController::class, 'update']);
            Route::delete('/', [ProfileController::class, 'destroy']);

            Route::post('/revoke-token', [ProfileController::class, 'revokeToken']);
        });
    });

    Route::middleware(['adult'])->group(function () {
        Route::get('accounts/balance', [AccountController::class, 'balance']);
        Route::apiResource('accounts', AccountController::class);
        Route::apiResource('accounts.cards', CardController::class)->only(['index', 'store']);
        Route::get('cards', [CardController::class, 'allCards']);
        Route::get('cards/{card}', [CardController::class, 'show']);
        Route::patch('cards/{card}/block', [CardController::class, 'block']);
        Route::patch('cards/{card}/unblock', [CardController::class, 'unblock']);
        Route::delete('cards/{card}', [CardController::class, 'destroy']);

        Route::get('transactions', [TransactionController::class, 'index']);
        Route::post('transactions', [TransactionController::class, 'store']);
        Route::get('transactions/{transaction}', [TransactionController::class, 'show']);
        Route::get('accounts/{account}/transactions', [TransactionController::class, 'accountTransactions']);
        Route::get('cards/{card}/transactions', [TransactionController::class, 'cardTransactions']);

        Route::post('flik/request-code/{card}', [FlikCodeController::class, 'requestCode']);
        Route::post('flik/status/{code}', [FlikCodeController::class, 'status']);
        Route::post('flik/pay', [FlikCodeController::class, 'pay']);
    });
});
