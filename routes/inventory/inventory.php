<?php

use Illuminate\Support\Facades\Route;
use App\Http\Modules\Inventory\Controller\InventoryController;

Route::middleware('auth:sanctum')->prefix('inventory')->group(function () {
    Route::get('products', [InventoryController::class, 'index']);
    Route::post('products', [InventoryController::class, 'store']);
    Route::put('products/{id}', [InventoryController::class, 'update']);
    Route::post('products/{id}/movements', [InventoryController::class, 'recordMovement']);
    Route::get('products/{id}/movements', [InventoryController::class, 'movements']);
});
