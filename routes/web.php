<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardFlagsController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\PlansController;

Route::get('/', function () {
    return ["Checkout API ON..."];
});

Route::controller(PlansController::class)->group(function () {
    Route::get('/plans/{id}', 'show');
    Route::get('/plans', 'index');
});

Route::controller(CardFlagsController::class)->group(function () {
    Route::get('/card-flags/{id}', 'show');
    Route::get('/card-flags', 'index');
});

Route::controller(CouponsController::class)->group(function () {
    Route::get('/coupons/{id}', 'showByPlanId');
    Route::get('/coupons', 'index');
});
