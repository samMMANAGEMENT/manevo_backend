<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\OperatorPayment\Controller\OperatorPaymentController;

Route::middleware('auth:sanctum')->prefix('payments')->group(function () {
    Route::get('/', [OperatorPaymentController::class, 'index']);
    Route::get('/pending-commissions', [OperatorPaymentController::class, 'pendingCommissions']);
    Route::post('/', [OperatorPaymentController::class, 'store']);
    Route::delete('/{payment}', [OperatorPaymentController::class, 'destroy']);
});
