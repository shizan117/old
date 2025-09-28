<?php

use Illuminate\Http\Request;
use \App\Http\Controllers\BkashController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

//bKash Bill Payment API
Route::post('/queryBill', [BkashController::class, 'queryBill']);
Route::post('/payBill', [BkashController::class, 'payBill']);
Route::post('/searchTransaction', [BkashController::class, 'searchTransaction']);
