<?php

use App\Http\Controllers\Api\WalletAuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('wallet')->group(function () {
    Route::post('nonce', [WalletAuthController::class, 'generateNonce']);
    Route::post('verify', [WalletAuthController::class, 'verify']);
});
