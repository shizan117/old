<?php

use App\Http\Controllers\RocketBillPayController;

Route::group(['prefix' => 'client/rocket-bill-pay'], function () {
    Route::post('/paymentValidation', [RocketBillPayController::class, 'paymentValidation'])->name('rocket.paymentValidation');

    Route::post('/paymentConfirmation', [RocketBillPayController::class, 'paymentConfirmation'])->name('rocket.paymentConfirmation');
    
    Route::post('/getPaymentStatus', [RocketBillPayController::class, 'getPaymentStatus'])->name('rocket.getPaymentStatus');
});