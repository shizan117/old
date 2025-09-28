<?php

use App\Http\Controllers\BkashCheckoutController;

Route::group(['prefix' => 'client/bkash-checkout', 'middleware' => 'auth.admin'], function () {
    Route::post('/update_bkash_checkout_settings', [BkashCheckoutController::class, 'update_checkout_settings'])->name('bkash_checkout_settings.update');
});

Route::post('/genarate_grant_token', [BkashCheckoutController::class, 'genarate_grant_token'])->name('grant_token.create')->prefix('bkash/bkash-checkout');