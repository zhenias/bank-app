<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Middleware\CheckToken;

Route::middleware(['auth:api'])->prefix('/token/revoke')->group(function () {
    Route::post('/', function (Request $request) {
        return response()->json(null);
    });
});

Route::middleware('guest')->group(function () {
    Route::post('/register', [RegisteredUserController::class, 'storeApi']);
});

Route::middleware(['auth:api'])->prefix('/user')->group(function () {
    Route::middleware([CheckToken::using('user-profile')])->get('/', function (Request $request) {
        return $request->user();
    });

    Route::middleware([CheckToken::using('user-balance')])->get('/balance', function (Request $request) {
        return [
            'balance' => 1250.50,
            'currency' => 'PLN',
        ];
    });
});
