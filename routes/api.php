<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CardFlagsController;
use App\Http\Controllers\CouponsController;
use App\Http\Controllers\PlansController;
use App\Http\Controllers\SubscriptionsController;

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
    Route::post('/coupons-validate', 'validateCoupon');
});

Route::controller(SubscriptionsController::class)->group(function () {
    Route::get('/subscriptions/{id}', 'show');
    Route::get('/subscriptions', 'index');
});

Route::middleware([\Infinitypaul\Idempotency\Middleware\EnsureIdempotency::class])
    ->group(function () {
        Route::post('/subscriptions', [SubscriptionsController::class, 'store']);
    });
