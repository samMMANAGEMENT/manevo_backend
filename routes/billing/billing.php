<?php

use App\Http\Modules\Billing\Controller\BillingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('billing/pending', [BillingController::class, 'getPendingItems']);
    Route::get('billing', [BillingController::class, 'index']);
    Route::post('billing', [BillingController::class, 'store']);
    Route::post('billing/{invoice}/send', [BillingController::class, 'sendToProvider']);
});
