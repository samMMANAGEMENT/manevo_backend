<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Sales\Controller\SalesController;

Route::middleware('auth:sanctum')->prefix('sales')->group(function () {
    Route::get('/', [SalesController::class, 'index']);
    Route::post('/', [SalesController::class, 'store']);
});
